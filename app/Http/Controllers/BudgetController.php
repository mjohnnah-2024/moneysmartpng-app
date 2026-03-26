<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\UsageTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('Y-m'));
        $monthDate = \Carbon\Carbon::parse($month . '-01');

        $budgets = $user->budgets()
            ->where('month', $monthDate->startOfMonth())
            ->orderBy('category')
            ->get();

        // Get actual spending per category for this month
        $spending = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()])
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => (float) $items->sum('amount'));

        $budgetsWithSpending = $budgets->map(fn (Budget $budget) => [
            'id' => $budget->id,
            'category' => $budget->category,
            'amount_limit' => (float) $budget->amount_limit,
            'month' => $budget->month->format('Y-m-d'),
            'spent' => $spending->get($budget->category, 0),
            'percentage' => $budget->amount_limit > 0
                ? round(($spending->get($budget->category, 0) / (float) $budget->amount_limit) * 100, 1)
                : 0,
        ]);

        $totalBudget = $budgets->sum('amount_limit');
        $totalSpent = $budgetsWithSpending->sum('spent');

        return Inertia::render('budgets/index', [
            'budgets' => $budgetsWithSpending->values(),
            'month' => $month,
            'totalBudget' => (float) $totalBudget,
            'totalSpent' => $totalSpent,
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('budgets/create', [
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $request->user()->budgets()->create($request->validated());

        UsageTracking::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'feature' => 'budgets',
                'period' => now()->format('Y-m'),
            ],
            ['count' => 0],
        )->increment('count');

        return redirect()->route('budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    public function edit(Budget $budget): Response
    {
        Gate::authorize('update', $budget);

        return Inertia::render('budgets/edit', [
            'budget' => $budget,
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        Gate::authorize('update', $budget);

        $budget->update($request->validated());

        return redirect()->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        Gate::authorize('delete', $budget);

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }
}
