<?php

namespace App\Models;

use Database\Factories\SquadChallengeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'squad_id',
    'name',
    'target_amount',
    'starts_at',
    'ends_at',
    'is_active',
])]
class SquadChallenge extends Model
{
    /** @use HasFactory<SquadChallengeFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Squad, $this> */
    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    /** @return HasMany<SquadChallengeProgress, $this> */
    public function progress(): HasMany
    {
        return $this->hasMany(SquadChallengeProgress::class, 'challenge_id');
    }
}
