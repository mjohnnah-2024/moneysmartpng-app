<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $transactions = $user->transactions()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        $spendingByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn ($items, $category) => [
                'category' => $category,
                'amount' => $items->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values()
            ->all();

        $recentTransactions = $transactions->take(5)->values()->all();

        return Inertia::render('dashboard', [
            'profile' => $user->profile,
            'summary' => [
                'totalIncome' => (float) $totalIncome,
                'totalExpense' => (float) $totalExpense,
                'remaining' => (float) ($totalIncome - $totalExpense),
                'month' => $now->format('F Y'),
            ],
            'spendingByCategory' => $spendingByCategory,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
