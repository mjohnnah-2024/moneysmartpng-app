<?php

namespace App\Models;

use Database\Factories\StreakFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'current_count',
    'longest_count',
    'last_recorded_at',
])]
class Streak extends Model
{
    /** @use HasFactory<StreakFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'current_count' => 0,
        'longest_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'current_count' => 'integer',
            'longest_count' => 'integer',
            'last_recorded_at' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
