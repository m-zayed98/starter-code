<?php

namespace App\Http\Requests\Contracts;

interface AuthLoginRequestContract
{
    /**
     * Return the attribute name used as the auth key (e.g. 'email', 'phone').
     */
    public function getAuthKey(): string;
}
