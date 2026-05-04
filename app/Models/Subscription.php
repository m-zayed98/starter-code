<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ad_package_id',
        'starts_at',
        'expires_at',
        'is_cancelled',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'    => 'date',
            'expires_at'   => 'date',
            'is_cancelled' => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adPackage(): BelongsTo
    {
        return $this->belongsTo(AdPackage::class);
    }
}
