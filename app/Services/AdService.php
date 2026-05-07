<?php

namespace App\Services;

use App\DTOs\NhcAdDataDTO;
use App\Enums\AdStatus;
use App\Facades\MediaUpload;
use App\Models\Ad;
use App\Repositories\Contracts\AdRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdService
{
    public function __construct(
        private readonly AdRepositoryContract   $adRepository,
        private readonly UserRepositoryContract $userRepository,
    ) {}

    // ─── Public API ───────────────────────────────────────────────────────

    /**
     * Step 1 – Initiate ad creation.
     *
     * Flow:
     *  1. Validate that no ad with the same ad_license_number exists.
     *  2. Persist FAL advertiser profile fields to the user (first time only).
     *  3. Call NHC (mock) to fetch property data.
     *  4. Create the ad in DRAFT status with NHC data stored in nhc_data JSON.
     *  5. Return the newly created Ad.
     *
     * @param int   $userId
     * @param array $data  Validated payload from InitiateAdRequest
     * @return Ad
     *
     * @throws \DomainException  When an ad with the same ad_license_number already exists.
     */
    public function initiateAd(int $userId, array $data): Ad
    {
        $adLicenseNumber = $data['ad_license_number'];

        // Guard: prevent duplicate ads
        if ($this->adRepository->existsByAdLicenseNumber($adLicenseNumber)) {
            throw new \DomainException(
                __('An ad with this advertisement license number already exists.')
            );
        }

        return DB::transaction(function () use ($userId, $data, $adLicenseNumber) {
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

            // Create the ad in DRAFT status
            /** @var Ad $ad */
            $ad = $this->adRepository->create([
                'user_id'            => $userId,
                'fal_license_number' => $data['fal_license_number'],
                'ad_license_number'  => $adLicenseNumber,
                'nhc_data'           => $nhcDto->toArray(),
                'status'             => AdStatus::DRAFT->value,
            ]);

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
     * @param int   $adId
     * @param int   $userId
     * @param array $data  Validated payload from UpdateAdRequest
     * @return Ad
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  When ad not found or not owned by user.
     */
    public function updateAd(int $adId, int $userId, array $data): Ad
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        return DB::transaction(function () use ($ad, $data) {
            // Extract file fields before persisting scalar data
            $coverImage       = Arr::pull($data, 'cover_image');
            $apartmentImages  = Arr::pull($data, 'apartment_images', []);
            $apartmentVideo   = Arr::pull($data, 'apartment_video');

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

            if (!empty($apartmentImages)) {
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
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function showUserAd(int $adId, int $userId): Ad
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        return $ad;
    }

    /**
     * Delete an ad owned by the given user.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteAd(int $adId, int $userId): void
    {
        $ad = $this->adRepository->findForUser($adId, $userId);

        if ($ad === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Ad #{$adId} not found for user #{$userId}."
            );
        }

        $this->adRepository->delete($ad->id);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────

    /**
     * Sync FAL advertiser profile fields to the user record.
     *
     * Fields are only written if they are not already set, so subsequent
     * ad creations do not overwrite the existing profile.
     *
     * @param int   $userId
     * @param array $data
     */
    private function syncUserFalProfile(int $userId, array $data): void
    {
        $user = $this->userRepository->showOrFail($userId);

        $profileUpdate = [];

        if (empty($user->fal_license_number) && !empty($data['fal_license_number'])) {
            $profileUpdate['fal_license_number'] = $data['fal_license_number'];
        }

        if (empty($user->nhc_mobile) && !empty($data['nhc_mobile'])) {
            $profileUpdate['nhc_mobile'] = $data['nhc_mobile'];
        }

        if (empty($user->advertiser_type) && !empty($data['advertiser_type'])) {
            $profileUpdate['advertiser_type'] = $data['advertiser_type'];
        }

        if (
            empty($user->commercial_registration_number)
            && !empty($data['commercial_registration_number'])
        ) {
            $profileUpdate['commercial_registration_number'] = $data['commercial_registration_number'];
        }

        if (!empty($profileUpdate)) {
            $this->userRepository->update($userId, $profileUpdate);
        }
    }

    /**
     * Upload the commercial registration document to the user's media collection.
     *
     * @param int          $userId
     * @param UploadedFile $file
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
     * NHC service. Here it returns realistic dummy data to simulate the response.
     *
     * All field-name mapping is handled inside NhcAdDataDTO::fromNhcResponse(),
     * so if NHC renames a field only the DTO needs updating.
     *
     * @param string $adLicenseNumber
     * @param string $falLicenseNumber
     * @param string $nhcMobile
     * @return NhcAdDataDTO
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
        $mockResponse = [
            'isValid'                            => true,
            'advertiserId'                       => '1234567890',
            'adLicenseNumber'                    => $adLicenseNumber,
            'advertiserName'                     => 'محمد عبدالله',
            'phoneNumber'                        => $nhcMobile,
            'brokerageAndMarketingLicenseNumber' => $falLicenseNumber,
            'deedNumber'                         => 'DEED-' . random_int(100000, 999999),
            'propertyPrice'                      => '850000',
            'propertyType'                       => 'شقة',
            'propertyAge'                        => 'أقل من عام',
            'advertisementType'                  => 'بيع',
            'propertyFace'                       => 'شمالية',
            'propertyUsages'                     => ['سكني'],
            'propertyArea'                       => '180.5',
            'streetWidth'                        => '15',
            'numberOfRooms'                      => '4',
            'planNumber'                         => 'PLN-' . strtoupper(substr($adLicenseNumber, 0, 6)),
            'landNumber'                         => (string) random_int(1000, 9999),
            'guaranteesAndTheirDuration'         => 'ضمان المقاول لمدة سنة',
            'obligationsOnTheProperty'           => 'لا يوجد',
            'ownershipTransferFeeType'           => 'Owner Contract Approver (مسئول اعتماد عقد المالك)',
            'LocationDescriptionOnMOJDeed'       => 'شقة سكنية في حي النرجس، الرياض',
            'propertyUtilities'                  => ['كهرباء', 'مياه', 'صرف صحي'],
            'responsibleEmployeeName'            => 'محمد عبدالله',
            'responsibleEmployeePhoneNumber'     => '0512345678',
            'location'                           => [
                [
                    'region'    => 'الرياض',
                    'city'      => 'الرياض',
                    'district'  => 'النرجس',
                    'latitude'  => '24.7136',
                    'longitude' => '46.6753',
                ],
            ],
            'adLicenseURL'  => 'https://nhc.gov.sa/license/' . $adLicenseNumber,
            'creationDate'  => now()->toDateString(),
            'endDate'       => now()->addYear()->toDateString(),
        ];

        return NhcAdDataDTO::fromNhcResponse($mockResponse);
    }
}
