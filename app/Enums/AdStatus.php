<?php

namespace App\Enums;

enum AdStatus: string
{
    case DRAFT     = 'draft';
    case PUBLISHED = 'published';
    case EXPIRED   = 'expired';
    case REJECTED  = 'rejected';

    public function label(): string
    {
        return __($this->value);
    }
}
