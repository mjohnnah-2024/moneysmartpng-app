<?php

namespace App\Models;

use Database\Factories\SquadMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'squad_id',
    'user_id',
    'role',
    'joined_at',
])]
class SquadMember extends Model
{
    /** @use HasFactory<SquadMemberFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'role' => 'member',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Squad, $this> */
    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
