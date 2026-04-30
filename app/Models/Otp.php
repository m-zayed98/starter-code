<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Otp extends Model
{
    protected $fillable = [
        'code',
        'purpose',
        'additional_data',
        'is_used',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function otpable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markUsed(): void
    {
        if ($this->is_used) {
            return;
        }

        $this->forceFill([
            'is_used' => true,
            'used_at' => now(),
        ])->save();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

