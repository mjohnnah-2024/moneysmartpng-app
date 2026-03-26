<?php

namespace App\Models;

use Database\Factories\SquadChallengeProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'challenge_id',
    'user_id',
    'current_amount',
])]
class SquadChallengeProgress extends Model
{
    /** @use HasFactory<SquadChallengeProgressFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'current_amount' => 0,
    ];

    protected function casts(): array
    {
        return [
            'current_amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<SquadChallenge, $this> */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(SquadChallenge::class, 'challenge_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
