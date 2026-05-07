<?php

namespace App\Enums;

enum AdvertiserType: string
{
    case BROKER           = 'broker';
    case INVESTOR         = 'investor';
    case APPRAISER        = 'appraiser';
    case PROPERTY_MANAGER = 'property_manager';
    case DEVELOPER        = 'developer';

    public function label(): string
    {
        return __($this->value);
    }

    /**
     * Advertiser types that require a commercial registration number.
     */
    public function requiresCommercialRegistration(): bool
    {
        return in_array($this, [self::DEVELOPER, self::INVESTOR], true);
    }
}
