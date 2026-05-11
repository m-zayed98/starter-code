<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'user_id',
        'reason',
        'reply',
        'status',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
