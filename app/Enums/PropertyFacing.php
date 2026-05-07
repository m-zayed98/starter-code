<?php

namespace App\Enums;

enum PropertyFacing: string
{
    case EAST  = 'east';
    case SOUTH = 'south';
    case WEST  = 'west';
    case NORTH = 'north';

    public function label(): string
    {
        return __($this->value);
    }
}
