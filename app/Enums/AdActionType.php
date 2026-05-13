<?php

namespace App\Enums;

enum AdActionType: string
{
    case VIEW      = 'view';
    case CALL      = 'call';
    case WHATSAPP  = 'whatsapp';
}
