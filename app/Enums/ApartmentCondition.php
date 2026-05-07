<?php

namespace App\Enums;

enum ApartmentCondition: string
{
    case NEW          = 'new';
    case USED         = 'used';
    case UNDER_CONSTRUCTION = 'under_construction';

    public function label(): string
    {
        return __($this->value);
    }
}
