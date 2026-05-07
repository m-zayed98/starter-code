<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING   = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED    = 'failed';

    public function label(): string
    {
        return __($this->value);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::FAILED]);
    }
}
