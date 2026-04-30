<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\PhoneNumberType;

class ValidMobilePhone implements ValidationRule
{
    public function __construct(
        private readonly ?string $dialCode
    ) {}


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fullNumber = '+' . ltrim($this->dialCode, '+') . $value;

        try {
            $phone = phone($fullNumber);

            if (!$phone->isValid()) {
                $fail(__('The :attribute is not a valid phone number for the given dial code.'));
                return;
            }

            $type = $phone->getType();

            $mobileTypes = [
                PhoneNumberType::MOBILE,
                PhoneNumberType::FIXED_LINE_OR_MOBILE,
            ];

            if (!in_array($type, $mobileTypes)) {
                $fail(__('The :attribute must be a mobile number.'));
            }
        } catch (\Exception) {
            $fail(__('The :attribute is not a valid mobile number for the given dial code.'));
        }
    }
}
