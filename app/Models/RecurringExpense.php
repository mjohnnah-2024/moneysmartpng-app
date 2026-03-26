<?php

namespace App\Models;

use Database\Factories\RecurringExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'name',
    'amount',
    'category',
    'frequency',
    'due_day',
    'is_paid',
    'last_paid_at',
    'is_active',
])]
class RecurringExpense extends Model
{
    /** @use HasFactory<RecurringExpenseFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_paid' => false,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_day' => 'integer',
            'is_paid' => 'boolean',
            'last_paid_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
