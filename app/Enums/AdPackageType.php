<?php

namespace App\Enums;

enum AdPackageType: string
{
    case NORMAL = 'normal';
    case OFFER  = 'offer';

    public function label(): string
    {
        return __($this->value);
    }
}
