<?php

namespace App\Enums;

enum NotificationType: string
{
    case FREE_PERIOD_ENABLED = 'free_period_enabled';
    case FREE_PERIOD_DISABLED = 'free_period_disabled';
    case ADMIN_NOTIFICATION = 'admin_notification';
    case GENERAL = 'general';

    public function label(): string
    {
        return __($this->value);
    }
}
