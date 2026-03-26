<?php

namespace App\Http\Controllers;

use App\Services\RecommendationsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsightsController extends Controller
{
    public function __invoke(Request $request, RecommendationsService $recommendationsService): Response
    {
        $user = $request->user();
        $now = now();

        // Current month data
        $currentStart = $now->copy()->startOfMonth();
        $currentEnd = $now->copy()->endOfMonth();
        $currentTransactions = $user->transactions()
            ->whereBetween('date', [$currentStart, $currentEnd])
            ->get();

        // Previous month data
        $prevStart = $now->copy()->subMonth()->startOfMonth();
        $prevEnd = $now->copy()->subMonth()->endOfMonth();
        $prevTransactions = $user->transactions()
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->get();

        // Spending by category (current month)
        $spendingByCategory = $currentTransactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn ($items, $category) => [
                'category' => $category,
                'amount' => (float) $items->sum('amount'),
                'count' => $items->count(),
            ])
            ->sortByDesc('amount')
            ->values()
            ->all();

        // Month-over-month comparison (last 6 months)
        $monthlyTrends = collect(range(5, 0))->map(function ($i) use ($user) {
            $month = now()->subMonths($i);
            $transactions = $user->transactions()
                ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->get();

            return [
                'month' => $month->format('M'),
                'income' => (float) $transactions->where('type', 'income')->sum('amount'),
                'expense' => (float) $transactions->where('type', 'expense')->sum('amount'),
            ];
        })->values()->all();

        // Daily spending trend (current month)
        $dailySpending = $currentTransactions
            ->where('type', 'expense')
            ->groupBy(fn ($t) => $t->date->format('d'))
            ->map(fn ($items, $day) => [
                'day' => (int) $day,
                'amount' => (float) $items->sum('amount'),
            ])
            ->sortBy('day')
            ->values()
            ->all();

        // Budget alerts
        $budgets = $user->budgets()
            ->where('month', $currentStart)
            ->get();

        $alerts = [];
        foreach ($budgets as $budget) {
            $spent = $currentTransactions
                ->where('type', 'expense')
                ->where('category', $budget->category)
                ->sum('amount');

            $pct = $budget->amount_limit > 0
                ? round(($spent / (float) $budget->amount_limit) * 100, 1)
                : 0;

            if ($pct >= 90) {
                $alerts[] = [
                    'type' => 'danger',
                    'category' => $budget->category,
                    'message' => $pct >= 100
                        ? "You've exceeded your {$budget->category} budget!"
                        : "You're at {$pct}% of your {$budget->category} budget.",
                    'percentage' => $pct,
                    'spent' => (float) $spent,
                    'limit' => (float) $budget->amount_limit,
                ];
            } elseif ($pct >= 75) {
                $alerts[] = [
                    'type' => 'warning',
                    'category' => $budget->category,
                    'message' => "You've used {$pct}% of your {$budget->category} budget.",
                    'percentage' => $pct,
                    'spent' => (float) $spent,
                    'limit' => (float) $budget->amount_limit,
                ];
            }
        }

        // Comparison with previous month
        $currentExpense = (float) $currentTransactions->where('type', 'expense')->sum('amount');
        $prevExpense = (float) $prevTransactions->where('type', 'expense')->sum('amount');
        $currentIncome = (float) $currentTransactions->where('type', 'income')->sum('amount');
        $prevIncome = (float) $prevTransactions->where('type', 'income')->sum('amount');

        $comparison = [
            'currentExpense' => $currentExpense,
            'prevExpense' => $prevExpense,
            'expenseChange' => $prevExpense > 0 ? round((($currentExpense - $prevExpense) / $prevExpense) * 100, 1) : 0,
            'currentIncome' => $currentIncome,
            'prevIncome' => $prevIncome,
            'incomeChange' => $prevIncome > 0 ? round((($currentIncome - $prevIncome) / $prevIncome) * 100, 1) : 0,
            'currentSavings' => $currentIncome - $currentExpense,
            'prevSavings' => $prevIncome - $prevExpense,
        ];

        return Inertia::render('insights', [
            'spendingByCategory' => $spendingByCategory,
            'monthlyTrends' => $monthlyTrends,
            'dailySpending' => $dailySpending,
            'alerts' => $alerts,
            'comparison' => $comparison,
            'recommendations' => $recommendationsService->generate($user),
        ]);
    }
}
