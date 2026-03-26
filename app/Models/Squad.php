<?php

namespace App\Models;

use Database\Factories\SquadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'creator_id',
    'invite_code',
    'max_members',
    'is_active',
])]
class Squad extends Model
{
    /** @use HasFactory<SquadFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'max_members' => 10,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'max_members' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /** @return HasMany<SquadMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(SquadMember::class);
    }

    /** @return HasMany<SquadChallenge, $this> */
    public function challenges(): HasMany
    {
        return $this->hasMany(SquadChallenge::class);
    }
}
