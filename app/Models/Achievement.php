<?php

namespace App\Models;

use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'badge_key',
    'points',
    'earned_at',
])]
class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'earned_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
