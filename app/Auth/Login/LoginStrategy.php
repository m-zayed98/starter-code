<?php

namespace App\Auth\Login;

use Illuminate\Contracts\Auth\Authenticatable;

interface LoginStrategy
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model&Authenticatable> $authModel
     * @return array<string, mixed>
     */
    public function login(
        string $guard,
        string $loginKey,
        string $authModel,
        array $credentials
    ): array;
}

