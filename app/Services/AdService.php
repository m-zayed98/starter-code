<?php

namespace App\Services;

use App\DTOs\NhcAdDataDTO;
use App\Enums\AdStatus;
use App\Exceptions\AdException;
use App\Facades\MediaUpload;
use App\Models\Ad;
use App\Repositories\Contracts\AdRepositoryContract;
use App\Repositories\Contracts\SubscriptionRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Settings\GeneralSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdService
{
    public function __construct(
        private readonly AdRepositoryContract           $adRepository,
        private readonly UserRepositoryContract         $userRepository,
        private readonly SubscriptionRepositoryContract $subscriptionRepository,
        private readonly GeneralSetting                 $generalSetting,
    ) {}

    // ─── Public API ───────────────────────────────────────────────────────

    /**
     * Step 1 – Initiate ad creation.
     *
     * Flow:
     *  1. Validate subscription rules (configurable via config/ads.php).
     *  2. Validate that no ad with the same ad_license_number exists.
     *  3. Persist FAL advertiser profile fields to the user (first time only).
     *  4. Call NHC (mock) to fetch property data.
     *  5. Create the ad in DRAFT status with NHC data stored in nhc_data JSON.
     *  6. Increment user_ads_count on the active subscription (if any).
     *  7. Return the newly created Ad.
     *
     * @param  array  $data  Validated payload from InitiateAdRequest
     *
     * @throws AdException               When subscription rules are violated.
     * @throws \DomainException          When an ad with the same ad_license_number already exists.
     */
    public function initiateAd(int $userId, array $data): Ad
    {
        // ── Subscription guards ───────────────────────────────────────────
        $subscription = $this->assertCanCreateAd($userId);

        $adLicenseNumber = $data['ad_license_number'];

        // Guard: prevent duplicate ads
        if ($this->adRepository->existsByAdLicenseNumber($adLicenseNumber)) {
            throw new \DomainException(
                __('An ad with this advertisement license number already exists.')
            );
        }

        return DB::transaction(function () use ($userId, $data, $adLicenseNumber, $subscription) {
            // Persist FAL profile to user (only fields that are not yet set)
            $this->syncUserFalProfile($userId, $data);

            // Call NHC to retrieve property data
            $nhcDto = $this->callMockNhcApi(
                adLicenseNumber: $adLicenseNumber,
                falLicenseNumber: $data['fal_license_number'],
                nhcMobile: $data['nhc_mobile'],
            );

            // Handle commercial registration file upload if present
            // (uploaded to user model, not ad model)
            if (isset($data['commercial_registration_file'])) {
                $this->uploadCommercialRegistration($userId, $data['commercial_registration_file']);
            }

            // Create the ad in DRAFT status, stamping the active package_id (if any)
            /** @var Ad $ad */
            $ad = $this->adRepository->create([
                'user_id'            => $userId,
                'package_id'         => $subscription?->ad_package_id,
                'fal_license_number' => $data['fal_license_number'],
                'ad_license_number' => $adLicenseNumber,
                'nhc_data' => $nhcDto->toArray(),
                'status' => AdStatus::DRAFT->value,
            ]);

            // Increment the subscription's used-ads counter (when a subscription is active)
            if ($subscription !== null) {
                $this->subscriptionRepository->incrementUserAdsCount($subscription->id);
            }

            return $ad->load('media');
        });
    }

    /**
     * Step 2 & 3 & 4 – Update an existing draft ad with the remaining fields.
     *
     * Accepts all user-editable fields from steps 2, 3, and 4.
     * Handles media uploads (cover image, apartment images, video).
     * Publishes the ad when all required fields are present.
     *
     * @param  array  $data  Validated payload from UpdateAdRequest
     *
     * @throws ModelNotFoundException When ad not found or not owned by user.
     */
    public function updateAd(int $adId, int $userId, array $data): Ad
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        return DB::transaction(function () use ($ad, $data) {
            // Extract file fields before persisting scalar data
            $coverImage = Arr::pull($data, 'cover_image');
            $apartmentImages = Arr::pull($data, 'apartment_images', []);
            $apartmentVideo = Arr::pull($data, 'apartment_video');

            // UpdateAdRequest validates ALL required fields — if we reach here
            // the ad is complete, so promote it to published immediately.
            $data['status'] = AdStatus::PUBLISHED->value;

            // Persist scalar fields + status
            /** @var Ad $updated */
            $updated = $this->adRepository->update($ad->id, $data);

            // Handle media uploads
            if ($coverImage instanceof UploadedFile) {
                $updated->clearMediaCollection('cover_image');
                MediaUpload::file($coverImage)
                    ->collection('cover_image')
                    ->uploadTo($updated);
            }

            if (! empty($apartmentImages)) {
                foreach ($apartmentImages as $image) {
                    if ($image instanceof UploadedFile) {
                        MediaUpload::file($image)
                            ->collection('apartment_images')
                            ->uploadTo($updated);
                    }
                }
            }

            if ($apartmentVideo instanceof UploadedFile) {
                $updated->clearMediaCollection('apartment_video');
                MediaUpload::file($apartmentVideo)
                    ->collection('apartment_video')
                    ->uploadTo($updated);
            }

            return $updated->load('media')->refresh();
        });
    }

    /**
     * Return a paginated list of ads for the given user.
     */
    public function listUserAds(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->adRepository->paginateForUser($userId, $perPage);
    }

    /**
     * Return a single ad owned by the given user.
     *
     * @throws ModelNotFoundException
     */
    public function showUserAd(int $adId, int $userId): Ad
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        return $ad;
    }

    /**
     * Delete an ad owned by the given user.
     *
     * @throws ModelNotFoundException
     */
    public function deleteAd(int $adId, int $userId): void
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        $this->adRepository->delete($ad->id);
    }

    /**
     * Toggle an ad owned by the given user between published and unpublished (rejected).
     *
     * - published  → rejected  (hidden from public listing)
     * - any other  → published (visible in public listing)
     *
     * Only ads that have been fully completed (not draft) can be toggled.
     *
     * @throws ModelNotFoundException
     * @throws \DomainException When the ad is still a draft.
     */
    public function toggleStatus(int $adId, int $userId): Ad
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        if ($ad->status === AdStatus::DRAFT) {
            throw new \DomainException(
                __('Cannot change the status of an incomplete ad.')
            );
        }

        $newStatus = $ad->status === AdStatus::PUBLISHED
            ? AdStatus::REJECTED
            : AdStatus::PUBLISHED;

        $this->adRepository->update($adId, ['status' => $newStatus->value]);

        return $this->adRepository->findForUser($adId, $userId);
    }

    /**
     * Return statistics for the authenticated user's ads.
     *
     * @return array{published_ads_count: int, unpublished_ads_count: int, total_views: int}
     */
    public function getUserAdStats(int $userId): array
    {
        return [
            'published_ads_count' => $this->adRepository->countPublishedForUser($userId),
            'unpublished_ads_count' => $this->adRepository->countUnpublishedForUser($userId),
            'total_views' => $this->adRepository->sumViewsForUser($userId),
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────

    /**
     * Validate subscription rules before allowing ad creation.
     *
     * Rules (each individually configurable via config/ads.php):
     *
     *  1. ENABLE_AD_CREATION_WITHOUT_PACKAGE (default: false)
     *     When false, the user must have an active subscription.
     *     Skipped entirely when the free_period is enabled in GeneralSetting.
     *
     *  2. ALLOW_ADDING_INFINITE_ADS (default: false)
     *     When false, the user cannot exceed their subscription's ads_count quota.
     *
     * @return \App\Models\Subscription|null  The active subscription (or null during free period).
     *
     * @throws AdException
     */
    private function assertCanCreateAd(int $userId): ?\App\Models\Subscription
    {
        $isFreePeriod = $this->generalSetting->is_free_period_enabled;

        // During a free period all subscription checks are bypassed
        if ($isFreePeriod) {
            return null;
        }

        $subscription = $this->subscriptionRepository->findActiveByUser($userId);

        // Guard 1: require an active subscription (unless config allows creation without one)
        if (! config('ads.ENABLE_AD_CREATION_WITHOUT_PACKAGE') && $subscription === null) {
            throw AdException::noActiveSubscription();
        }

        // Guard 2: enforce quota (unless config allows infinite ads)
        if (
            ! config('ads.ALLOW_ADDING_INFINITE_ADS')
            && $subscription !== null
            && ! $subscription->hasQuota()
        ) {
            throw AdException::quotaExceeded();
        }

        return $subscription;
    }

    /**
     * Sync FAL advertiser profile fields to the user record.
     *
     * Fields are only written if they are not already set, so subsequent
     * ad creations do not overwrite the existing profile.
     */
    private function syncUserFalProfile(int $userId, array $data): void
    {
        $user = $this->userRepository->showOrFail($userId);

        $profileUpdate = [];

        if (empty($user->fal_license_number) && ! empty($data['fal_license_number'])) {
            $profileUpdate['fal_license_number'] = $data['fal_license_number'];
        }

        if (empty($user->nhc_mobile) && ! empty($data['nhc_mobile'])) {
            $profileUpdate['nhc_mobile'] = $data['nhc_mobile'];
        }

        if (empty($user->advertiser_type) && ! empty($data['advertiser_type'])) {
            $profileUpdate['advertiser_type'] = $data['advertiser_type'];
        }

        if (
            empty($user->commercial_registration_number)
            && ! empty($data['commercial_registration_number'])
        ) {
            $profileUpdate['commercial_registration_number'] = $data['commercial_registration_number'];
        }

        if (! empty($profileUpdate)) {
            $this->userRepository->update($userId, $profileUpdate);
        }
    }

    /**
     * Upload the commercial registration document to the user's media collection.
     */
    private function uploadCommercialRegistration(int $userId, UploadedFile $file): void
    {
        $user = $this->userRepository->showOrFail($userId);

        $user->clearMediaCollection('commercial_registration');

        MediaUpload::file($file)
            ->collection('commercial_registration')
            ->uploadTo($user);
    }

    // ─── Mock NHC API ─────────────────────────────────────────────────────

    /**
     * Mock implementation of the NHC (National Housing Company) API call.
     *
     * In production this would make an authenticated HTTP request to the real
     * NHC service. Here it picks one of 10 realistic templates (keyed by
     * ad_license_number) so each license returns different property data —
     * same license always gets the same shape for repeatable testing.
     *
     * All field-name mapping is handled inside NhcAdDataDTO::fromNhcResponse(),
     * so if NHC renames a field only the DTO needs updating.
     */
    private function callMockNhcApi(
        string $adLicenseNumber,
        string $falLicenseNumber,
        string $nhcMobile,
    ): NhcAdDataDTO {
        // Mock response using the EXACT key names from the real NHC API
        // (GET /v2/brokerage/AdvertisementValidator).
        // When integrating the real API, replace this method body with an
        // HTTP call and pass the raw response directly to fromNhcResponse().
        $templates = $this->mockNhcTemplates();
        $index = abs(crc32($adLicenseNumber)) % count($templates);
        $template = $templates[$index];

        $mockResponse = array_merge($template, [
            'isValid' => true,
            'adLicenseNumber' => $adLicenseNumber,
            'phoneNumber' => $nhcMobile,
            'brokerageAndMarketingLicenseNumber' => $falLicenseNumber,
            'deedNumber' => 'DEED-' . (100000 + ($index * 11111) + (abs(crc32($adLicenseNumber)) % 9000)),
            'planNumber' => 'PLN-' . strtoupper(substr($adLicenseNumber, 0, 6) ?: 'MOCK' . $index),
            'landNumber' => (string) (1000 + ($index * 137) + (abs(crc32($adLicenseNumber)) % 800)),
            'adLicenseURL' => 'https://nhc.gov.sa/license/' . $adLicenseNumber,
            'creationDate' => now()->subDays($index * 7)->toDateString(),
            'endDate' => now()->subDays($index * 7)->addYear()->toDateString(),
        ]);

        return NhcAdDataDTO::fromNhcResponse($mockResponse);
    }

    /**
     * Ten varied NHC-shaped property samples for local/testing use.
     * Selection is deterministic via ad_license_number hash.
     *
     * @return list<array<string, mixed>>
     */
    private function mockNhcTemplates(): array
    {
        return [
            // 1 — شقة للبيع، الرياض / النرجس
            [
                'advertiserId' => '1000000001',
                'advertiserName' => 'محمد عبدالله العتيبي',
                'propertyPrice' => '850000',
                'propertyType' => 'شقة',
                'propertyAge' => 'أقل من عام',
                'advertisementType' => 'بيع',
                'propertyFace' => 'شمالية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '180.5',
                'streetWidth' => '15',
                'numberOfRooms' => '4',
                'guaranteesAndTheirDuration' => 'ضمان المقاول لمدة سنة',
                'obligationsOnTheProperty' => 'لا يوجد',
                'ownershipTransferFeeType' => 'Owner Contract Approver (مسئول اعتماد عقد المالك)',
                'LocationDescriptionOnMOJDeed' => 'شقة سكنية في حي النرجس، الرياض',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي'],
                'responsibleEmployeeName' => 'محمد عبدالله العتيبي',
                'responsibleEmployeePhoneNumber' => '0512345678',
                'location' => [[
                    'region' => 'الرياض',
                    'city' => 'الرياض',
                    'district' => 'النرجس',
                    'latitude' => '24.8301',
                    'longitude' => '46.6552',
                ]],
            ],
            // 2 — فيلا للبيع، جدة / أبحر
            [
                'advertiserId' => '1000000002',
                'advertiserName' => 'سارة فهد الغامدي',
                'propertyPrice' => '2450000',
                'propertyType' => 'فيلا',
                'propertyAge' => 'من 1 إلى 5 سنوات',
                'advertisementType' => 'بيع',
                'propertyFace' => 'غربية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '420',
                'streetWidth' => '20',
                'numberOfRooms' => '6',
                'guaranteesAndTheirDuration' => 'ضمان الهيكل لمدة 10 سنوات',
                'obligationsOnTheProperty' => 'رسوم صيانة سنوية على المجمع',
                'ownershipTransferFeeType' => 'Buyer (المشتري)',
                'LocationDescriptionOnMOJDeed' => 'فيلا مستقلة في حي أبحر الشمالية، جدة',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'غاز'],
                'responsibleEmployeeName' => 'خالد أحمد الزهراني',
                'responsibleEmployeePhoneNumber' => '0559876543',
                'location' => [[
                    'region' => 'مكة المكرمة',
                    'city' => 'جدة',
                    'district' => 'أبحر الشمالية',
                    'latitude' => '21.7519',
                    'longitude' => '39.1386',
                ]],
            ],
            // 3 — شقة للإيجار، الدمام / الشاطئ
            [
                'advertiserId' => '1000000003',
                'advertiserName' => 'عبدالرحمن سعد القحطاني',
                'propertyPrice' => '35000',
                'propertyType' => 'شقة',
                'propertyAge' => 'من 5 إلى 10 سنوات',
                'advertisementType' => 'إيجار',
                'propertyFace' => 'شرقية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '145',
                'streetWidth' => '12',
                'numberOfRooms' => '3',
                'guaranteesAndTheirDuration' => 'لا يوجد',
                'obligationsOnTheProperty' => 'دفع الإيجار مقدماً كل ستة أشهر',
                'ownershipTransferFeeType' => 'N/A',
                'LocationDescriptionOnMOJDeed' => 'شقة مفروشة جزئياً في حي الشاطئ، الدمام',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'إنترنت'],
                'responsibleEmployeeName' => 'عبدالرحمن سعد القحطاني',
                'responsibleEmployeePhoneNumber' => '0533445566',
                'location' => [[
                    'region' => 'المنطقة الشرقية',
                    'city' => 'الدمام',
                    'district' => 'الشاطئ',
                    'latitude' => '26.4282',
                    'longitude' => '50.1131',
                ]],
            ],
            // 4 — أرض للبيع، الرياض / الملقا
            [
                'advertiserId' => '1000000004',
                'advertiserName' => 'نورة إبراهيم الشمري',
                'propertyPrice' => '3200',
                'propertyType' => 'أرض',
                'propertyAge' => 'جديد',
                'advertisementType' => 'بيع',
                'propertyFace' => 'جنوبية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '625',
                'streetWidth' => '18',
                'numberOfRooms' => '0',
                'guaranteesAndTheirDuration' => 'لا يوجد',
                'obligationsOnTheProperty' => 'خاضعة لأنظمة البلدية',
                'ownershipTransferFeeType' => 'Shared (مشترك)',
                'LocationDescriptionOnMOJDeed' => 'أرض سكنية خام في حي الملقا، الرياض',
                'propertyUtilities' => ['كهرباء', 'مياه'],
                'responsibleEmployeeName' => 'فيصل ناصر الدوسري',
                'responsibleEmployeePhoneNumber' => '0541122334',
                'location' => [[
                    'region' => 'الرياض',
                    'city' => 'الرياض',
                    'district' => 'الملقا',
                    'latitude' => '24.7915',
                    'longitude' => '46.6078',
                ]],
            ],
            // 5 — محل تجاري للإيجار، الرياض / العليا
            [
                'advertiserId' => '1000000005',
                'advertiserName' => 'مؤسسة النخبة العقارية',
                'propertyPrice' => '120000',
                'propertyType' => 'محل',
                'propertyAge' => 'من 10 إلى 20 سنة',
                'advertisementType' => 'إيجار',
                'propertyFace' => 'شمالية شرقية',
                'propertyUsages' => ['تجاري'],
                'propertyArea' => '95',
                'streetWidth' => '30',
                'numberOfRooms' => '1',
                'guaranteesAndTheirDuration' => 'لا يوجد',
                'obligationsOnTheProperty' => 'تأمين إيجار يعادل شهرين',
                'ownershipTransferFeeType' => 'N/A',
                'LocationDescriptionOnMOJDeed' => 'محل تجاري على شارع رئيسي في حي العليا، الرياض',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'تكييف مركزي'],
                'responsibleEmployeeName' => 'ماجد سليمان الحربي',
                'responsibleEmployeePhoneNumber' => '0567788990',
                'location' => [[
                    'region' => 'الرياض',
                    'city' => 'الرياض',
                    'district' => 'العليا',
                    'latitude' => '24.6932',
                    'longitude' => '46.6851',
                ]],
            ],
            // 6 — دور للإيجار، المدينة المنورة / العزيزية
            [
                'advertiserId' => '1000000006',
                'advertiserName' => 'يوسف عمر الأنصاري',
                'propertyPrice' => '28000',
                'propertyType' => 'دور',
                'propertyAge' => 'من 1 إلى 5 سنوات',
                'advertisementType' => 'إيجار',
                'propertyFace' => 'جنوبية غربية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '220',
                'streetWidth' => '16',
                'numberOfRooms' => '5',
                'guaranteesAndTheirDuration' => 'صيانة مجانية لأول 3 أشهر',
                'obligationsOnTheProperty' => 'لا يوجد',
                'ownershipTransferFeeType' => 'N/A',
                'LocationDescriptionOnMOJDeed' => 'دور علوي في فيلا مزدوجة، حي العزيزية، المدينة المنورة',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي'],
                'responsibleEmployeeName' => 'يوسف عمر الأنصاري',
                'responsibleEmployeePhoneNumber' => '0502233445',
                'location' => [[
                    'region' => 'المدينة المنورة',
                    'city' => 'المدينة المنورة',
                    'district' => 'العزيزية',
                    'latitude' => '24.4672',
                    'longitude' => '39.6111',
                ]],
            ],
            // 7 — شقة للبيع، الخبر / الراكة
            [
                'advertiserId' => '1000000007',
                'advertiserName' => 'هند ماجد العجمي',
                'propertyPrice' => '720000',
                'propertyType' => 'شقة',
                'propertyAge' => 'من 5 إلى 10 سنوات',
                'advertisementType' => 'بيع',
                'propertyFace' => 'بحرية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '165',
                'streetWidth' => '14',
                'numberOfRooms' => '3',
                'guaranteesAndTheirDuration' => 'لا يوجد',
                'obligationsOnTheProperty' => 'رسوم اتحاد الملاك قائمة',
                'ownershipTransferFeeType' => 'Buyer (المشتري)',
                'LocationDescriptionOnMOJDeed' => 'شقة مطلة على البحر في حي الراكة، الخبر',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'مواقف سيارات'],
                'responsibleEmployeeName' => 'طلال حسين العجمي',
                'responsibleEmployeePhoneNumber' => '0590011223',
                'location' => [[
                    'region' => 'المنطقة الشرقية',
                    'city' => 'الخبر',
                    'district' => 'الراكة',
                    'latitude' => '26.2667',
                    'longitude' => '50.2083',
                ]],
            ],
            // 8 — فيلا للإيجار، الرياض / الياسمين
            [
                'advertiserId' => '1000000008',
                'advertiserName' => 'شركة دار الإسكان للعقارات',
                'propertyPrice' => '95000',
                'propertyType' => 'فيلا',
                'propertyAge' => 'أقل من عام',
                'advertisementType' => 'إيجار',
                'propertyFace' => 'ثلاث واجهات',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '380',
                'streetWidth' => '25',
                'numberOfRooms' => '7',
                'guaranteesAndTheirDuration' => 'صيانة شاملة لمدة سنة',
                'obligationsOnTheProperty' => 'عقد إيجار موحد عبر إيجار',
                'ownershipTransferFeeType' => 'N/A',
                'LocationDescriptionOnMOJDeed' => 'فيلا حديثة مفروشة في حي الياسمين، الرياض',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'غاز', 'إنترنت', 'حراسة'],
                'responsibleEmployeeName' => 'ريم عبدالله السبيعي',
                'responsibleEmployeePhoneNumber' => '0576655443',
                'location' => [[
                    'region' => 'الرياض',
                    'city' => 'الرياض',
                    'district' => 'الياسمين',
                    'latitude' => '24.8210',
                    'longitude' => '46.6405',
                ]],
            ],
            // 9 — استراحة للبيع، الطائف / الشفا
            [
                'advertiserId' => '1000000009',
                'advertiserName' => 'بندر علي الثقفي',
                'propertyPrice' => '1100000',
                'propertyType' => 'استراحة',
                'propertyAge' => 'من 1 إلى 5 سنوات',
                'advertisementType' => 'بيع',
                'propertyFace' => 'شمالية غربية',
                'propertyUsages' => ['سكني', 'ترفيهي'],
                'propertyArea' => '850',
                'streetWidth' => '10',
                'numberOfRooms' => '4',
                'guaranteesAndTheirDuration' => 'ضمان المسبح والمرافق لمدة سنتين',
                'obligationsOnTheProperty' => 'لا يوجد',
                'ownershipTransferFeeType' => 'Owner Contract Approver (مسئول اعتماد عقد المالك)',
                'LocationDescriptionOnMOJDeed' => 'استراحة مع مسبح وحديقة في منطقة الشفا، الطائف',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'بئر'],
                'responsibleEmployeeName' => 'بندر علي الثقفي',
                'responsibleEmployeePhoneNumber' => '0583344556',
                'location' => [[
                    'region' => 'مكة المكرمة',
                    'city' => 'الطائف',
                    'district' => 'الشفا',
                    'latitude' => '21.1667',
                    'longitude' => '40.3500',
                ]],
            ],
            // 10 — شقة للبيع، مكة / العوالي
            [
                'advertiserId' => '1000000010',
                'advertiserName' => 'أحمد حسن القرشي',
                'propertyPrice' => '980000',
                'propertyType' => 'شقة',
                'propertyAge' => 'من 10 إلى 20 سنة',
                'advertisementType' => 'بيع',
                'propertyFace' => 'شرقية',
                'propertyUsages' => ['سكني'],
                'propertyArea' => '155',
                'streetWidth' => '12',
                'numberOfRooms' => '3',
                'guaranteesAndTheirDuration' => 'لا يوجد',
                'obligationsOnTheProperty' => 'رهن جزئي لدى البنك الأهلي',
                'ownershipTransferFeeType' => 'Shared (مشترك)',
                'LocationDescriptionOnMOJDeed' => 'شقة في برج سكني بحي العوالي، مكة المكرمة',
                'propertyUtilities' => ['كهرباء', 'مياه', 'صرف صحي', 'مصعد'],
                'responsibleEmployeeName' => 'أحمد حسن القرشي',
                'responsibleEmployeePhoneNumber' => '0524455667',
                'location' => [[
                    'region' => 'مكة المكرمة',
                    'city' => 'مكة المكرمة',
                    'district' => 'العوالي',
                    'latitude' => '21.3891',
                    'longitude' => '39.8579',
                ]],
            ],
        ];
    }
}
