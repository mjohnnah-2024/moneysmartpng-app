<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class RecommendationsService
{
    /**
     * Generate personalized recommendations for a user.
     *
     * @return array<int, array{id: string, type: string, message: string, action?: array{label: string, href: string}}>
     */
    public function generate(User $user): array
    {
        $recommendations = [];
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $this->checkDailyLimitWarning($user, $now, $startOfMonth, $endOfMonth, $recommendations);
        $this->checkCategoryReduction($user, $startOfMonth, $endOfMonth, $recommendations);
        $this->checkSavingsOpportunity($user, $startOfMonth, $endOfMonth, $recommendations);
        $this->checkGoalPaceWarning($user, $now, $recommendations);
        $this->checkIncomeReminder($user, $now, $startOfMonth, $endOfMonth, $recommendations);
        $this->checkBillReminder($user, $now, $recommendations);

        return array_slice($recommendations, 0, 5);
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function checkDailyLimitWarning(User $user, Carbon $now, Carbon $startOfMonth, Carbon $endOfMonth, array &$recommendations): void
    {
        $safeToSpend = app(SafeToSpendService::class)->calculate($user);

        $todaySpending = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereDate('date', $now->toDateString())
            ->sum('amount');

        if ($safeToSpend['daily'] > 0 && $todaySpending >= $safeToSpend['daily'] * 0.8) {
            $recommendations[] = [
                'id' => 'daily_limit_warning',
                'type' => 'warning',
                'message' => "You've spent most of your safe daily amount. Try to keep spending low for the rest of today.",
                'action' => ['label' => 'View Dashboard', 'href' => '/dashboard'],
            ];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function checkCategoryReduction(User $user, Carbon $startOfMonth, Carbon $endOfMonth, array &$recommendations): void
    {
        $budgets = $user->budgets()
            ->where('month', '>=', $startOfMonth)
            ->where('month', '<=', $endOfMonth)
            ->get();

        foreach ($budgets as $budget) {
            $spending = (float) $user->transactions()
                ->where('type', 'expense')
                ->where('category', $budget->category)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $limit = (float) $budget->amount_limit;
            if ($limit > 0 && $spending > $limit * 1.2) {
                $overspend = round($spending - $limit, 2);
                $recommendations[] = [
                    'id' => "category_reduction_{$budget->category}",
                    'type' => 'warning',
                    'message' => "You're over budget on {$budget->category} by K{$overspend}. Try to cut back this week.",
                    'action' => ['label' => 'View Budgets', 'href' => '/budgets'],
                ];
                break;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function checkSavingsOpportunity(User $user, Carbon $startOfMonth, Carbon $endOfMonth, array &$recommendations): void
    {
        $budgets = $user->budgets()
            ->where('month', '>=', $startOfMonth)
            ->where('month', '<=', $endOfMonth)
            ->get();

        foreach ($budgets as $budget) {
            $spending = (float) $user->transactions()
                ->where('type', 'expense')
                ->where('category', $budget->category)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $limit = (float) $budget->amount_limit;
            if ($limit > 0 && $spending < $limit * 0.5 && $spending > 0) {
                $savings = round($limit - $spending, 2);
                $recommendations[] = [
                    'id' => "savings_opportunity_{$budget->category}",
                    'type' => 'success',
                    'message' => "You're under budget on {$budget->category} — move K{$savings} to savings?",
                    'action' => ['label' => 'View Goals', 'href' => '/goals'],
                ];
                break;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function checkGoalPaceWarning(User $user, Carbon $now, array &$recommendations): void
    {
        $goals = $user->goals()
            ->where('status', 'active')
            ->whereNotNull('deadline')
            ->get();

        foreach ($goals as $goal) {
            $remaining = (float) $goal->target_amount - (float) $goal->current_amount;
            if ($remaining <= 0) {
                continue;
            }

            $daysLeft = max(1, $now->diffInDays($goal->deadline));
            $weeksLeft = max(1, ceil($daysLeft / 7));
            $weeklyNeeded = $remaining / $weeksLeft;

            if ($weeklyNeeded > ((float) $user->profile->monthly_income ?? 0) * 0.1) {
                $recommendations[] = [
                    'id' => "goal_pace_{$goal->id}",
                    'type' => 'info',
                    'message' => 'Increase weekly savings by K'.round($weeklyNeeded, 0)." to reach \"{$goal->name}\" on time.",
                    'action' => ['label' => 'View Goals', 'href' => '/goals'],
                ];
                break;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function checkIncomeReminder(User $user, Carbon $now, Carbon $startOfMonth, Carbon $endOfMonth, array &$recommendations): void
    {
        if ($now->day < 5) {
            return;
        }

        $incomeCount = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();

        if ($incomeCount === 0) {
            $recommendations[] = [
                'id' => 'income_reminder',
                'type' => 'info',
                'message' => 'Have you received your pay this month? Log your income to keep your budget accurate.',
                'action' => ['label' => 'Add Income', 'href' => '/transactions/create'],
            ];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    private function checkBillReminder(User $user, Carbon $now, array &$recommendations): void
    {
        $upcomingBills = $user->recurringExpenses()
            ->where('is_active', true)
            ->where('is_paid', false)
            ->where('due_day', '>=', $now->day)
            ->where('due_day', '<=', $now->day + 3)
            ->orderBy('due_day')
            ->get();

        foreach ($upcomingBills as $bill) {
            $daysUntil = $bill->due_day - $now->day;
            $recommendations[] = [
                'id' => "bill_reminder_{$bill->id}",
                'type' => 'warning',
                'message' => "{$bill->name} (K".number_format((float) $bill->amount, 2).") due in {$daysUntil} day".($daysUntil !== 1 ? 's' : '').'.',
                'action' => ['label' => 'View Bills', 'href' => '/bills'],
            ];
        }
    }
}
