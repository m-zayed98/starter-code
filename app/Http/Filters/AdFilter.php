<?php

namespace App\Http\Filters;

/**
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  AdFilter                                                               │
 * │                                                                         │
 * │  Handles all public ad listing filters.                                 │
 * │                                                                         │
 * │  Scalar filters (ads table columns):                                    │
 * │    search          – full-text across title, advertiser name, location  │
 * │    purpose         – sale | rent                                        │
 * │    apartment_condition – new | used | under_construction                │
 * │    rental_period   – daily | weekly | monthly | yearly                  │
 * │    furnishing_status – furnished | unfurnished                          │
 * │    price_min       – minimum price                                      │
 * │    price_max       – maximum price                                      │
 * │                                                                         │
 * │  JSON path filters (nhc_data column – stored by NhcAdDataDTO::toArray)  │
 * │    property_type   – nhc_data->property_type                            │
 * │    region          – nhc_data->region                                   │
 * │    city            – nhc_data->city                                     │
 * │    district        – nhc_data->district                                 │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
class AdFilter extends BaseFilters
{
    protected $filters = [
        // ── Scalar (ads table) ────────────────────────────────────────────
        'search',
        'purpose',
        'apartment_condition',
        'rental_period',
        'furnishing_status',
        'price_min',
        'price_max',

        // ── JSON path (nhc_data column) ───────────────────────────────────
        'property_type',
        'region',
        'city',
        'district',
    ];

    // ─── Scalar filters ───────────────────────────────────────────────────

    /**
     * Full-text search across:
     *  - ads.title
     *  - ads.description
     *  - nhc_data->advertiser_name   (اسم المعلن)
     *  - nhc_data->region            (المنطقة)
     *  - nhc_data->city              (المدينة)
     *  - nhc_data->district          (الحي)
     *  - nhc_data->property_type     (نوع العقار)
     *  - ads.fal_license_number      (رقم رخصة فال)
     *  - ads.ad_license_number       (رقم رخصة الإعلان)
     */
    protected function search(string $value): void
    {
        $term = "%{$value}%";

        $this->builder->where(function ($query) use ($term) {
            $query->where('title', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('fal_license_number', 'like', $term)
                ->orWhere('ad_license_number', 'like', $term)
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.advertiser_name')) LIKE ?", [$term])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.region')) LIKE ?", [$term])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.city')) LIKE ?", [$term])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.district')) LIKE ?", [$term])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.property_type')) LIKE ?", [$term]);
        });
    }

    /**
     * Filter by ad purpose: sale | rent
     */
    protected function purpose(string $value): void
    {
        $this->builder->where('purpose', $value);
    }

    /**
     * Filter by apartment condition: new | used | under_construction
     */
    protected function apartmentCondition(string $value): void
    {
        $this->builder->where('apartment_condition', $value);
    }

    /**
     * Filter by rental period: daily | weekly | monthly | yearly
     * Only meaningful when purpose = rent.
     */
    protected function rentalPeriod(string $value): void
    {
        $this->builder->where('rental_period', $value);
    }

    /**
     * Filter by furnishing status: furnished | unfurnished
     */
    protected function furnishingStatus(string $value): void
    {
        $this->builder->where('furnishing_status', $value);
    }

    /**
     * Filter by minimum price (inclusive).
     */
    protected function priceMin(string $value): void
    {
        $this->builder->where('price', '>=', (float) $value);
    }

    /**
     * Filter by maximum price (inclusive).
     */
    protected function priceMax(string $value): void
    {
        $this->builder->where('price', '<=', (float) $value);
    }

    // ─── JSON path filters (nhc_data) ─────────────────────────────────────

    /**
     * Filter by property type stored in nhc_data->property_type
     * e.g. "شقة", "فيلا", "أرض", "محل"
     */
    protected function propertyType(string $value): void
    {
        $this->builder->whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.property_type')) = ?",
            [$value]
        );
    }

    /**
     * Filter by region stored in nhc_data->region
     */
    protected function region(string $value): void
    {
        $this->builder->whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.region')) LIKE ?",
            ["%{$value}%"]
        );
    }

    /**
     * Filter by city stored in nhc_data->city
     */
    protected function city(string $value): void
    {
        $this->builder->whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.city')) LIKE ?",
            ["%{$value}%"]
        );
    }

    /**
     * Filter by district stored in nhc_data->district
     */
    protected function district(string $value): void
    {
        $this->builder->whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(nhc_data, '$.district')) LIKE ?",
            ["%{$value}%"]
        );
    }
}
