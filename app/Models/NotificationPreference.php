<?php

namespace App\Models;

use Database\Factories\NotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'in_app',
    'push',
    'enabled',
])]
class NotificationPreference extends Model
{
    /** @use HasFactory<NotificationPreferenceFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'in_app' => true,
        'push' => false,
        'enabled' => true,
    ];

    protected function casts(): array
    {
        return [
            'in_app' => 'boolean',
            'push' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
