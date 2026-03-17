<?php

namespace App\Enums;

enum ContactMessageType: string
{
    case REQUEST = 'request';
    case SUGGESTION = 'suggestion';
    case INQUIRY = 'inquiry';
    case COMPLAINT = 'complaint';
    case OTHER = 'other';
}

