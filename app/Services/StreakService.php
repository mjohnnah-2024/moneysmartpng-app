<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Streak;
use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    public const BADGE_DEFINITIONS = [
        'first_transaction' => ['name' => 'First Step', 'description' => 'Log your first transaction', 'points' => 10],
        'budget_creator' => ['name' => 'Planner', 'description' => 'Create your first budget', 'points' => 10],
        'goal_setter' => ['name' => 'Dreamer', 'description' => 'Create your first savings goal', 'points' => 10],
        'goal_completed' => ['name' => 'Achievement Unlocked', 'description' => 'Complete a savings goal', 'points' => 50],
        '3_day_streak' => ['name' => 'Getting Started', 'description' => '3 consecutive days under budget', 'points' => 15],
        '7_day_streak' => ['name' => 'Steady Saver', 'description' => '7-day streak', 'points' => 30],
        '30_day_streak' => ['name' => 'Money Master', 'description' => '30-day streak', 'points' => 100],
        'savings_50' => ['name' => 'Halfway There', 'description' => '50% of any goal reached', 'points' => 25],
        'first_referral' => ['name' => 'Social Saver', 'description' => 'First successful referral', 'points' => 30],
        '10_transactions' => ['name' => 'Tracker', 'description' => '10 transactions logged', 'points' => 15],
        'safe_spender' => ['name' => 'Under Control', 'description' => '7 days within safe-to-spend', 'points' => 40],
    ];

    /**
     * Update a user's streak for a given type.
     */
    public function updateStreak(User $user, string $type): Streak
    {
        $streak = $user->streaks()->firstOrCreate(
            ['type' => $type],
            ['current_count' => 0, 'longest_count' => 0]
        );

        $today = Carbon::today();

        if ($streak->last_recorded_at && $streak->last_recorded_at->isToday()) {
            return $streak;
        }

        if ($streak->last_recorded_at && $streak->last_recorded_at->isYesterday()) {
            $streak->current_count++;
        } else {
            $streak->current_count = 1;
        }

        if ($streak->current_count > $streak->longest_count) {
            $streak->longest_count = $streak->current_count;
        }

        $streak->last_recorded_at = $today;
        $streak->save();

        return $streak;
    }

    /**
     * Break a streak (set current_count to 0).
     */
    public function breakStreak(User $user, string $type): void
    {
        $user->streaks()->where('type', $type)->update(['current_count' => 0]);
    }

    /**
     * Check and award achievements for a user.
     *
     * @return array<string> Newly awarded badge keys
     */
    public function checkAchievements(User $user): array
    {
        $awarded = [];
        $existing = $user->achievements()->pluck('badge_key')->toArray();

        // first_transaction
        if (! in_array('first_transaction', $existing) && $user->transactions()->count() >= 1) {
            $this->awardBadge($user, 'first_transaction');
            $awarded[] = 'first_transaction';
        }

        // 10_transactions
        if (! in_array('10_transactions', $existing) && $user->transactions()->count() >= 10) {
            $this->awardBadge($user, '10_transactions');
            $awarded[] = '10_transactions';
        }

        // budget_creator
        if (! in_array('budget_creator', $existing) && $user->budgets()->count() >= 1) {
            $this->awardBadge($user, 'budget_creator');
            $awarded[] = 'budget_creator';
        }

        // goal_setter
        if (! in_array('goal_setter', $existing) && $user->goals()->count() >= 1) {
            $this->awardBadge($user, 'goal_setter');
            $awarded[] = 'goal_setter';
        }

        // goal_completed
        if (! in_array('goal_completed', $existing) && $user->goals()->where('status', 'completed')->count() >= 1) {
            $this->awardBadge($user, 'goal_completed');
            $awarded[] = 'goal_completed';
        }

        // savings_50
        if (! in_array('savings_50', $existing)) {
            $halfwayGoal = $user->goals()
                ->where('status', 'active')
                ->whereRaw('current_amount >= target_amount * 0.5')
                ->exists();

            if ($halfwayGoal) {
                $this->awardBadge($user, 'savings_50');
                $awarded[] = 'savings_50';
            }
        }

        // Streak-based badges
        $underBudgetStreak = $user->streaks()->where('type', 'under_budget')->first();
        $currentCount = $underBudgetStreak?->current_count ?? 0;

        if (! in_array('3_day_streak', $existing) && $currentCount >= 3) {
            $this->awardBadge($user, '3_day_streak');
            $awarded[] = '3_day_streak';
        }

        if (! in_array('7_day_streak', $existing) && $currentCount >= 7) {
            $this->awardBadge($user, '7_day_streak');
            $awarded[] = '7_day_streak';
        }

        if (! in_array('30_day_streak', $existing) && $currentCount >= 30) {
            $this->awardBadge($user, '30_day_streak');
            $awarded[] = '30_day_streak';
        }

        // safe_spender (7 days within safe-to-spend)
        $safeSpenderStreak = $user->streaks()->where('type', 'savings_contribution')->first();
        if (! in_array('safe_spender', $existing) && ($safeSpenderStreak?->current_count ?? 0) >= 7) {
            $this->awardBadge($user, 'safe_spender');
            $awarded[] = 'safe_spender';
        }

        // first_referral
        if (! in_array('first_referral', $existing) && $user->profile) {
            $referralCount = Profile::where('referred_by', $user->profile->referral_code)->count();
            if ($referralCount >= 1) {
                $this->awardBadge($user, 'first_referral');
                $awarded[] = 'first_referral';
            }
        }

        return $awarded;
    }

    private function awardBadge(User $user, string $badgeKey): void
    {
        $definition = self::BADGE_DEFINITIONS[$badgeKey] ?? null;
        if (! $definition) {
            return;
        }

        $user->achievements()->create([
            'badge_key' => $badgeKey,
            'points' => $definition['points'],
            'earned_at' => now(),
        ]);
    }

    /**
     * Get all badge definitions with earned status for a user.
     *
     * @return array<int, array{badge_key: string, name: string, description: string, points: int, earned_at: string|null}>
     */
    public function getBadgesForUser(User $user): array
    {
        $earned = $user->achievements()->get()->keyBy('badge_key');

        return collect(self::BADGE_DEFINITIONS)->map(fn ($def, $key) => [
            'badge_key' => $key,
            'name' => $def['name'],
            'description' => $def['description'],
            'points' => $def['points'],
            'earned_at' => $earned->get($key)?->earned_at?->toIso8601String(),
        ])->values()->all();
    }

    /**
     * Get total points for a user.
     */
    public function getTotalPoints(User $user): int
    {
        return (int) $user->achievements()->sum('points');
    }
}
