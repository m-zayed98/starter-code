<?php

namespace App\Enums;

enum NafathVerificationStatus: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case EXPIRED  = 'expired';
}
