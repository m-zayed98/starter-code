<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDialCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dialCode = (int) ltrim($value, '+');

        $util = \libphonenumber\PhoneNumberUtil::getInstance();
        $regions = $util->getRegionCodesForCountryCode($dialCode);
        if (empty($regions) || $regions === ['001']) {
            $fail('The :attribute is not a valid dial code.');
        }
    }
}
