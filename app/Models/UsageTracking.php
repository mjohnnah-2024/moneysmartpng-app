<?php

namespace App\Models;

use Database\Factories\UsageTrackingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'feature',
    'count',
    'period',
])]
class UsageTracking extends Model
{
    /** @use HasFactory<UsageTrackingFactory> */
    use HasFactory;

    protected $table = 'usage_tracking';

    /** @var array<string, mixed> */
    protected $attributes = [
        'count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'count' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
