<?php

namespace App\Enums;

enum AdPurpose: string
{
    case SALE  = 'sale';
    case RENT  = 'rent';

    public function label(): string
    {
        return __($this->value);
    }
}
