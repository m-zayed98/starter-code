<?php

namespace App\Models;

use App\Enums\AdActionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'user_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => AdActionType::class,
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
