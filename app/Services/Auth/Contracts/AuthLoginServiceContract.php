<?php

namespace App\Services\Auth\Contracts;

interface AuthLoginServiceContract
{
    /**
     * Attempt login with validated credentials. Returns the full response data (e.g. user, token, permissions).
     *
     * @param array<string, mixed> $credentials Validated credentials (must contain auth key field and 'password')
     * @return array<string, mixed> Response data to pass to ApiResponse::respondWithArray()
     */
    public function login(array $credentials, string $authKey): array;
}
