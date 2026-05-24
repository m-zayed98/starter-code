<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case REPLIED = 'replied';
    case NOT_REPLITED = 'not_replited';

    public function label(): string
    {
        return __($this->value);
    }
}
