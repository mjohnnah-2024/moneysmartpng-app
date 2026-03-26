<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /** @return HasOne<Profile, $this> */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** @return HasMany<Budget, $this> */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /** @return HasMany<Goal, $this> */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /** @return HasMany<ChatMessage, $this> */
    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /** @return HasMany<UsageTracking, $this> */
    public function usageTracking(): HasMany
    {
        return $this->hasMany(UsageTracking::class);
    }

    /** @return HasOne<Subscription, $this> */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /** @return HasMany<ManualPayment, $this> */
    public function manualPayments(): HasMany
    {
        return $this->hasMany(ManualPayment::class);
    }

    /** @return HasMany<RecurringExpense, $this> */
    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class);
    }

    /** @return HasMany<NotificationPreference, $this> */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /** @return HasMany<Streak, $this> */
    public function streaks(): HasMany
    {
        return $this->hasMany(Streak::class);
    }

    /** @return HasMany<Achievement, $this> */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /** @return HasMany<SquadMember, $this> */
    public function squadMemberships(): HasMany
    {
        return $this->hasMany(SquadMember::class);
    }

    /** @return HasMany<Squad, $this> */
    public function createdSquads(): HasMany
    {
        return $this->hasMany(Squad::class, 'creator_id');
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->subscription;

        return $subscription !== null && $subscription->isActive();
    }

    public function activePlan(): string
    {
        return $this->hasActiveSubscription() ? 'premium' : 'free';
    }
}
