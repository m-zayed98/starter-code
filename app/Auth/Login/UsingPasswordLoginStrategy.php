<?php

namespace App\Auth\Login;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsingPasswordLoginStrategy implements LoginStrategy
{
    public function login(string $guard, string $loginKey, string $authModel, array $credentials): array
    {
        $loginValue = $credentials[$loginKey] ?? null;
        $password = $credentials['password'] ?? null;

        if (!is_string($loginValue) || $loginValue === '' || !is_string($password) || $password === '') {
            throw ValidationException::withMessages([
                $loginKey => ['Invalid credentials.'],
            ]);
        }

        /** @var \Illuminate\Database\Eloquent\Model&Authenticatable|null $user */
        $user = $authModel::query()->where($loginKey, $loginValue)->first();

        if (!$user || !Hash::check($password, (string) $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                $loginKey => ['Invalid credentials.'],
            ]);
        }
        $token = method_exists($user, 'createToken')
            ? $user->createToken($guard)->plainTextToken
            : null;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
