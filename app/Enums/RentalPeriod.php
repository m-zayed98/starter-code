<?php

namespace App\Enums;

enum RentalPeriod: string
{
    case DAILY   = 'daily';
    case WEEKLY  = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY  = 'yearly';

    public function label(): string
    {
        return __($this->value);
    }
}
