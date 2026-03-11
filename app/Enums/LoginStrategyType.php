<?php

namespace App\Enums;

use App\Auth\Login\LoginStrategy;
use App\Auth\Login\UsingOtpLoginStrategy;
use App\Auth\Login\UsingPasswordLoginStrategy;
use InvalidArgumentException;

enum LoginStrategyType: string
{
    case PASSWORD = 'password';
    case OTP = 'otp';

    /**
     * Create the concrete login strategy for this enum case.
     *
     * @param array<string, mixed> $parameters
     */
    public function make(array $parameters = []): LoginStrategy
    {
        /** @var class-string<LoginStrategy>|null $class */
        $class = match ($this) {
            self::PASSWORD => UsingPasswordLoginStrategy::class,
            self::OTP => UsingOtpLoginStrategy::class,
        };

        if ($class === null) {
            throw new InvalidArgumentException("Unsupported login strategy [{$this->value}].");
        }

        return app()->makeWith($class, $parameters);
    }
}

