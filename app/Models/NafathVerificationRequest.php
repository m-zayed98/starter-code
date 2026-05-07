<?php

namespace App\Models;

use App\Enums\NafathVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NafathVerificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'trans_id',
        'random_code',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'status'     => NafathVerificationStatus::class,
        ];
    }

    /******************************* Relationships **************/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /******************************* Helpers **************/

    public function isPending(): bool
    {
        return $this->status === NafathVerificationStatus::PENDING;
    }

    public function isExpired(): bool
    {
        return $this->status === NafathVerificationStatus::EXPIRED
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }
}
