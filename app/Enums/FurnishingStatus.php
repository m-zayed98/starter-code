<?php

namespace App\Enums;

enum FurnishingStatus: string
{
    case FURNISHED   = 'furnished';
    case UNFURNISHED = 'unfurnished';

    public function label(): string
    {
        return __($this->value);
    }
}
