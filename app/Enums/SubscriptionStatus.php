<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE    = 'active';
    case EXPIRED   = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return __($this->value);
    }
}
