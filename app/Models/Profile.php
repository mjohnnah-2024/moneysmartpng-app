<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'full_name',
    'phone_number',
    'preferred_language',
    'monthly_income',
    'plan',
    'referral_code',
    'referred_by',
    'premium_days_earned',
    'pay_cycle_type',
    'pay_cycle_start_day',
])]
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'preferred_language' => 'en',
        'plan' => 'free',
        'premium_days_earned' => 0,
        'pay_cycle_type' => 'monthly',
        'pay_cycle_start_day' => 1,
    ];

    protected function casts(): array
    {
        return [
            'monthly_income' => 'decimal:2',
            'premium_days_earned' => 'integer',
            'pay_cycle_start_day' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
