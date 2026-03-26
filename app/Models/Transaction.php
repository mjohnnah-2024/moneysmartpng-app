<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'amount',
    'type',
    'category',
    'description',
    'date',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const EXPENSE_CATEGORIES = [
        'Food/Market',
        'Transport PMV',
        'Airtime/Data',
        'Betelnut',
        'Church/Community',
        'School Fees',
        'Rent',
        'Utilities',
        'Medical',
        'Clothing',
        'Entertainment',
        'Other',
    ];

    public const INCOME_CATEGORIES = [
        'Salary',
        'Business',
        'Freelance',
        'Gift',
        'Other',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
