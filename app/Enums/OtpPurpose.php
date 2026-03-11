<?php

namespace App\Enums;

enum OtpPurpose: string
{
    case LOGIN = 'login';
    case REGISTER = 'register';
    case FORGOT_PASSWORD = 'forget_password';
}

