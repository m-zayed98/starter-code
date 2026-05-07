<?php

namespace App\Enums;

enum PropertyUsage: string
{
    case AGRICULTURAL = 'agricultural';
    case COMMERCIAL   = 'commercial';
    case INDUSTRIAL   = 'industrial';
    case HEALTH       = 'health';
    case EDUCATIONAL  = 'educational';
    case RESIDENTIAL  = 'residential';

    public function label(): string
    {
        return __($this->value);
    }
}
