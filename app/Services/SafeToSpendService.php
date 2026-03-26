<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class SafeToSpendService
{
    /**
     * Calculate the safe-to-spend data for a user.
     *
     * @return array{daily: float, remaining: float, percentage: float, status: string, nextBill: array{name: string, amount: float, days_until: int}|null, breakdown: array{availableBalance: float, pendingBills: float, goalContributions: float}}
     */
    public function calculate(User $user): array
    {
        $profile = $user->profile;
        $now = Carbon::now();

        [$cycleStart, $cycleEnd] = $this->getCycleDates($profile, $now);
        $remainingDays = max(1, $now->diffInDays($cycleEnd) + 1);

        $availableBalance = $this->getAvailableBalance($user, $cycleStart, $cycleEnd);
        $pendingBills = $this->getPendingBills($user, $now, $cycleEnd);
        $goalContributions = $this->getGoalContributions($user);

        $remaining = $availableBalance - $pendingBills - $goalContributions;
        $daily = $remaining / $remainingDays;

        $monthlyIncome = (float) ($profile->monthly_income ?? 0);
        $percentage = $monthlyIncome > 0
            ? round(($remaining / $monthlyIncome) * 100, 1)
            : 0;

        $status = match (true) {
            $percentage > 70 => 'green',
            $percentage > 30 => 'yellow',
            default => 'red',
        };

        $nextBill = $this->getNextBill($user, $now);

        return [
            'daily' => round(max(0, $daily), 2),
            'remaining' => round(max(0, $remaining), 2),
            'percentage' => $percentage,
            'status' => $status,
            'nextBill' => $nextBill,
            'breakdown' => [
                'availableBalance' => round($availableBalance, 2),
                'pendingBills' => round($pendingBills, 2),
                'goalContributions' => round($goalContributions, 2),
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getCycleDates(mixed $profile, Carbon $now): array
    {
        $cycleType = $profile->pay_cycle_type ?? 'monthly';
        $startDay = $profile->pay_cycle_start_day ?? 1;

        if ($cycleType === 'monthly') {
            $start = $now->copy()->day(min($startDay, $now->daysInMonth));
            if ($start->gt($now)) {
                $start->subMonth();
            }
            $end = $start->copy()->addMonth()->subDay();
        } elseif ($cycleType === 'fortnightly') {
            $start = $now->copy()->day(min($startDay, $now->daysInMonth));
            while ($start->gt($now)) {
                $start->subDays(14);
            }
            $end = $start->copy()->addDays(13);
        } else {
            $dayOfWeek = min($startDay, 7) - 1;
            $start = $now->copy()->startOfWeek()->addDays($dayOfWeek);
            if ($start->gt($now)) {
                $start->subWeek();
            }
            $end = $start->copy()->addDays(6);
        }

        return [$start, $end];
    }

    private function getAvailableBalance(User $user, Carbon $cycleStart, Carbon $cycleEnd): float
    {
        $monthlyIncome = (float) ($user->profile->monthly_income ?? 0);

        $transactions = $user->transactions()
            ->whereBetween('date', [$cycleStart, $cycleEnd])
            ->get();

        $transactionIncome = (float) $transactions->where('type', 'income')->sum('amount');
        $transactionExpenses = (float) $transactions->where('type', 'expense')->sum('amount');

        return $monthlyIncome + $transactionIncome - $transactionExpenses;
    }

    private function getPendingBills(User $user, Carbon $now, Carbon $cycleEnd): float
    {
        return (float) $user->recurringExpenses()
            ->where('is_active', true)
            ->where('is_paid', false)
            ->where('due_day', '>=', $now->day)
            ->where('due_day', '<=', $cycleEnd->day)
            ->sum('amount');
    }

    private function getGoalContributions(User $user): float
    {
        $activeGoals = $user->goals()
            ->where('status', 'active')
            ->whereNotNull('deadline')
            ->get();

        $total = 0;

        foreach ($activeGoals as $goal) {
            $remaining = (float) $goal->target_amount - (float) $goal->current_amount;
            if ($remaining <= 0) {
                continue;
            }

            $monthsLeft = max(1, Carbon::now()->diffInMonths($goal->deadline) + 1);
            $total += $remaining / $monthsLeft;
        }

        return $total;
    }

    /**
     * @return array{name: string, amount: float, days_until: int}|null
     */
    private function getNextBill(User $user, Carbon $now): ?array
    {
        $bill = $user->recurringExpenses()
            ->where('is_active', true)
            ->where('is_paid', false)
            ->where('due_day', '>=', $now->day)
            ->orderBy('due_day')
            ->first();

        if (! $bill) {
            return null;
        }

        return [
            'name' => $bill->name,
            'amount' => (float) $bill->amount,
            'days_until' => $bill->due_day - $now->day,
        ];
    }
}
