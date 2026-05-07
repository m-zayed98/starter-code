<?php

namespace App\DTOs;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  NhcAdDataDTO                                                           │
 * │                                                                         │
 * │  Maps the raw NHC Advertisement Validator API response (v2) into a      │
 * │  typed, immutable value object.                                         │
 * │                                                                         │
 * │  API: GET /v2/brokerage/AdvertisementValidator                          │
 * │                                                                         │
 * │  ALL NHC key-name mapping lives in fromNhcResponse().                   │
 * │  When integrating the real API, only that method needs updating —       │
 * │  the rest of the codebase reads typed properties and is untouched.      │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * NHC field → DTO property mapping reference:
 * ┌──────────────────────────────────────────┬──────────────────────────────────────┬──────────────────────────────────────────┐
 * │ NHC key (exact)                          │ DTO property                         │ Arabic label                             │
 * ├──────────────────────────────────────────┼──────────────────────────────────────┼──────────────────────────────────────────┤
 * │ isValid                                  │ isValid                              │ وجود ترخيص إعلان؟                        │
 * │ advertiserId                             │ advertiserId                         │ رقم هوية المعلن                          │
 * │ adLicenseNumber                          │ adLicenseNumber                      │ رقم ترخيص الإعلان                        │
 * │ deedNumber                               │ deedNumber                           │ رقم وثيقة الملكية                        │
 * │ advertiserName                           │ advertiserName                       │ اسم المعلن                               │
 * │ phoneNumber                              │ phoneNumber                          │ رقم التواصل للمعلن                       │
 * │ brokerageAndMarketingLicenseNumber       │ brokerageAndMarketingLicenseNumber   │ رقم رخصة الوساطة والتسويق العقاري        │
 * │ propertyPrice                            │ propertyPrice                        │ سعر الوحدة / سعر المتر للأرض             │
 * │ propertyType                             │ propertyType                         │ نوع العقار                               │
 * │ propertyAge                              │ propertyAge                          │ عمر العقار                               │
 * │ advertisementType                        │ advertisementType                    │ غرض الإعلان                              │
 * │ propertyArea                             │ propertyArea                         │ مساحة العقار                             │
 * │ streetWidth                              │ streetWidth                          │ عرض الشارع                               │
 * │ numberOfRooms                            │ numberOfRooms                        │ عدد الغرف                                │
 * │ propertyFace                             │ propertyFace                         │ واجهة العقار                             │
 * │ planNumber                               │ planNumber                           │ رقم المخطط                               │
 * │ landNumber                               │ landNumber                           │ رقم القطعة                               │
 * │ guaranteesAndTheirDuration               │ guaranteesAndTheirDuration           │ الضمانات ومدتها                          │
 * │ obligationsOnTheProperty                 │ obligationsOnTheProperty             │ الالتزامات على العقار                    │
 * │ propertyUsages                           │ propertyUsages                       │ استخدام العقار                           │
 * │ propertyUtilities                        │ propertyUtilities                    │ خدمات العقار                             │
 * │ LocationDescriptionOnMOJDeed             │ locationDescriptionOnMOJDeed         │ وصف موقع العقار حسب الصك                │
 * │ location[].region                        │ location.region                      │ المنطقة                                  │
 * │ location[].city                          │ location.city                        │ المدينة                                  │
 * │ location[].district                      │ location.district                    │ الحي                                     │
 * │ location[].latitude                      │ location.latitude                    │ خط العرض                                 │
 * │ location[].longitude                     │ location.longitude                   │ خط الطول                                 │
 * │ ownershipTransferFeeType                 │ ownershipTransferFeeType             │ رسوم نقل الملكية                         │
 * │ responsibleEmployeeName                  │ responsibleEmployeeName              │ مسؤول الإعلان                            │
 * │ responsibleEmployeePhoneNumber           │ responsibleEmployeePhoneNumber       │ رقم مسؤول الإعلان                        │
 * │ adLicenseURL                             │ adLicenseURL                         │ رابط ترخيص الإعلان                       │
 * │ creationDate                             │ creationDate                         │ تاريخ إنشاء ترخيص الإعلان                │
 * │ endDate                                  │ endDate                              │ تاريخ انتهاء ترخيص الإعلان               │
 * └──────────────────────────────────────────┴──────────────────────────────────────┴──────────────────────────────────────────┘
 */
final class NhcAdDataDTO
{
    public function __construct(
        // ── Validation flag ───────────────────────────────────────────────
        public readonly bool    $isValid,

        // ── Advertiser identity ───────────────────────────────────────────
        public readonly string  $advertiserId,
        public readonly string  $adLicenseNumber,
        public readonly string  $advertiserName,
        public readonly string  $phoneNumber,
        public readonly string  $brokerageAndMarketingLicenseNumber,

        // ── Ownership document ────────────────────────────────────────────
        public readonly string  $deedNumber,

        // ── Pricing ───────────────────────────────────────────────────────
        public readonly string  $propertyPrice,          // سعر الوحدة / سعر المتر للأرض

        // ── Property classification ───────────────────────────────────────
        public readonly string  $propertyType,           // نوع العقار (Lookup)
        public readonly string  $propertyAge,            // عمر العقار (Lookup)
        public readonly string  $advertisementType,      // غرض الإعلان: بيع | إيجار (Lookup)
        public readonly string  $propertyFace,           // واجهة العقار (Lookup)
        public readonly array   $propertyUsages,         // استخدام العقار (Array, Lookup)

        // ── Physical attributes ───────────────────────────────────────────
        public readonly string  $propertyArea,           // مساحة العقار
        public readonly string  $streetWidth,            // عرض الشارع
        public readonly string  $numberOfRooms,          // عدد الغرف

        // ── Plot identifiers ──────────────────────────────────────────────
        public readonly string  $planNumber,             // رقم المخطط
        public readonly string  $landNumber,             // رقم القطعة

        // ── Legal / obligations ───────────────────────────────────────────
        public readonly string  $guaranteesAndTheirDuration,   // الضمانات ومدتها
        public readonly string  $obligationsOnTheProperty,     // الالتزامات على العقار
        public readonly string  $ownershipTransferFeeType,     // رسوم نقل الملكية
        public readonly string  $locationDescriptionOnMOJDeed, // وصف موقع العقار حسب الصك

        // ── Utilities / services ──────────────────────────────────────────
        public readonly array   $propertyUtilities,      // خدمات العقار (Array, Lookup)

        // ── Responsible employee ──────────────────────────────────────────
        public readonly string  $responsibleEmployeeName,        // مسؤول الإعلان
        public readonly string  $responsibleEmployeePhoneNumber, // رقم مسؤول الإعلان

        // ── Location (nested object from NHC) ────────────────────────────
        public readonly string  $region,
        public readonly string  $city,
        public readonly string  $district,
        public readonly ?string $latitude,
        public readonly ?string $longitude,

        // ── License meta ──────────────────────────────────────────────────
        public readonly string  $adLicenseURL,
        public readonly string  $creationDate,
        public readonly string  $endDate,
    ) {}

    // ─── Factory ──────────────────────────────────────────────────────────

    /**
     * Build a DTO from the raw NHC API response array.
     *
     * This is the ONLY place where NHC key names are referenced.
     * If NHC renames a field in a future API version, update only here.
     *
     * @param array<string, mixed> $nhcResponse
     */
    public static function fromNhcResponse(array $nhcResponse): self
    {
        // location is returned as an array of objects; we take the first entry.
        $location = (array) ($nhcResponse['location'][0] ?? $nhcResponse['location'] ?? []);

        return new self(
            // Validation flag
            isValid:                            (bool)   ($nhcResponse['isValid']                            ?? false),

            // Advertiser identity
            advertiserId:                       (string) ($nhcResponse['advertiserId']                       ?? ''),
            adLicenseNumber:                    (string) ($nhcResponse['adLicenseNumber']                    ?? ''),
            advertiserName:                     (string) ($nhcResponse['advertiserName']                     ?? ''),
            phoneNumber:                        (string) ($nhcResponse['phoneNumber']                        ?? ''),
            brokerageAndMarketingLicenseNumber: (string) ($nhcResponse['brokerageAndMarketingLicenseNumber'] ?? ''),

            // Ownership document
            deedNumber:                         (string) ($nhcResponse['deedNumber']                         ?? ''),

            // Pricing
            propertyPrice:                      (string) ($nhcResponse['propertyPrice']                      ?? ''),

            // Property classification
            propertyType:                       (string) ($nhcResponse['propertyType']                       ?? ''),
            propertyAge:                        (string) ($nhcResponse['propertyAge']                        ?? ''),
            advertisementType:                  (string) ($nhcResponse['advertisementType']                  ?? ''),
            propertyFace:                       (string) ($nhcResponse['propertyFace']                       ?? ''),
            propertyUsages:                     (array)  ($nhcResponse['propertyUsages']                     ?? []),

            // Physical attributes
            propertyArea:                       (string) ($nhcResponse['propertyArea']                       ?? ''),
            streetWidth:                        (string) ($nhcResponse['streetWidth']                        ?? ''),
            numberOfRooms:                      (string) ($nhcResponse['numberOfRooms']                      ?? ''),

            // Plot identifiers
            planNumber:                         (string) ($nhcResponse['planNumber']                         ?? ''),
            landNumber:                         (string) ($nhcResponse['landNumber']                         ?? ''),

            // Legal / obligations
            guaranteesAndTheirDuration:         (string) ($nhcResponse['guaranteesAndTheirDuration']         ?? ''),
            obligationsOnTheProperty:           (string) ($nhcResponse['obligationsOnTheProperty']           ?? ''),
            ownershipTransferFeeType:           (string) ($nhcResponse['ownershipTransferFeeType']           ?? ''),
            locationDescriptionOnMOJDeed:       (string) ($nhcResponse['LocationDescriptionOnMOJDeed']       ?? ''),

            // Utilities / services
            propertyUtilities:                  (array)  ($nhcResponse['propertyUtilities']                  ?? []),

            // Responsible employee
            responsibleEmployeeName:            (string) ($nhcResponse['responsibleEmployeeName']            ?? ''),
            responsibleEmployeePhoneNumber:     (string) ($nhcResponse['responsibleEmployeePhoneNumber']     ?? ''),

            // Location (nested)
            region:                             (string) ($location['region']    ?? ''),
            city:                               (string) ($location['city']      ?? ''),
            district:                           (string) ($location['district']  ?? ''),
            latitude:                           isset($location['latitude'])  ? (string) $location['latitude']  : null,
            longitude:                          isset($location['longitude']) ? (string) $location['longitude'] : null,

            // License meta
            adLicenseURL:                       (string) ($nhcResponse['adLicenseURL']  ?? ''),
            creationDate:                       (string) ($nhcResponse['creationDate']  ?? ''),
            endDate:                            (string) ($nhcResponse['endDate']       ?? ''),
        );
    }

    // ─── Serialization ────────────────────────────────────────────────────

    /**
     * Convert the DTO to an array for storing in the `nhc_data` JSON column.
     * Keys here are our internal names — stable regardless of NHC API changes.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_valid'                              => $this->isValid,
            'advertiser_id'                         => $this->advertiserId,
            'ad_license_number'                     => $this->adLicenseNumber,
            'advertiser_name'                       => $this->advertiserName,
            'phone_number'                          => $this->phoneNumber,
            'brokerage_and_marketing_license_number'=> $this->brokerageAndMarketingLicenseNumber,
            'deed_number'                           => $this->deedNumber,
            'property_price'                        => $this->propertyPrice,
            'property_type'                         => $this->propertyType,
            'property_age'                          => $this->propertyAge,
            'advertisement_type'                    => $this->advertisementType,
            'property_face'                         => $this->propertyFace,
            'property_usages'                       => $this->propertyUsages,
            'property_area'                         => $this->propertyArea,
            'street_width'                          => $this->streetWidth,
            'number_of_rooms'                       => $this->numberOfRooms,
            'plan_number'                           => $this->planNumber,
            'land_number'                           => $this->landNumber,
            'guarantees_and_their_duration'         => $this->guaranteesAndTheirDuration,
            'obligations_on_the_property'           => $this->obligationsOnTheProperty,
            'ownership_transfer_fee_type'           => $this->ownershipTransferFeeType,
            'location_description_on_moj_deed'      => $this->locationDescriptionOnMOJDeed,
            'property_utilities'                    => $this->propertyUtilities,
            'responsible_employee_name'             => $this->responsibleEmployeeName,
            'responsible_employee_phone_number'     => $this->responsibleEmployeePhoneNumber,
            'region'                                => $this->region,
            'city'                                  => $this->city,
            'district'                              => $this->district,
            'latitude'                              => $this->latitude,
            'longitude'                             => $this->longitude,
            'ad_license_url'                        => $this->adLicenseURL,
            'creation_date'                         => $this->creationDate,
            'end_date'                              => $this->endDate,
        ];
    }
}
