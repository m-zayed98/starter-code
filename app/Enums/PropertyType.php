<?php

namespace App\Enums;

enum PropertyType: string
{
    case LAND       = 'land';
    case APARTMENT  = 'apartment';
    case VILLA      = 'villa';
    case SHOP       = 'shop';

    public function label(): string
    {
        return __($this->value);
    }
}
