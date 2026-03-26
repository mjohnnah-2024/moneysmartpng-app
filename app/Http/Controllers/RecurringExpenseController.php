<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringExpenseRequest;
use App\Http\Requests\UpdateRecurringExpenseRequest;
use App\Models\RecurringExpense;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RecurringExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $bills = $request->user()->recurringExpenses()
            ->where('is_active', true)
            ->orderBy('due_day')
            ->get()
            ->map(fn (RecurringExpense $bill) => [
                'id' => $bill->id,
                'name' => $bill->name,
                'amount' => (float) $bill->amount,
                'category' => $bill->category,
                'frequency' => $bill->frequency,
                'due_day' => $bill->due_day,
                'is_paid' => $bill->is_paid,
                'last_paid_at' => $bill->last_paid_at?->format('Y-m-d'),
            ]);

        $summary = [
            'totalBills' => $bills->count(),
            'totalAmount' => $bills->sum('amount'),
            'paidCount' => $bills->where('is_paid', true)->count(),
            'unpaidAmount' => $bills->where('is_paid', false)->sum('amount'),
        ];

        return Inertia::render('bills/index', [
            'bills' => $bills,
            'summary' => $summary,
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('bills/create', [
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function store(StoreRecurringExpenseRequest $request): RedirectResponse
    {
        $request->user()->recurringExpenses()->create($request->validated());

        return redirect()->route('bills.index')
            ->with('success', 'Recurring bill created successfully.');
    }

    public function edit(RecurringExpense $bill): Response
    {
        Gate::authorize('update', $bill);

        return Inertia::render('bills/edit', [
            'bill' => $bill,
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function update(UpdateRecurringExpenseRequest $request, RecurringExpense $bill): RedirectResponse
    {
        Gate::authorize('update', $bill);

        $bill->update($request->validated());

        return redirect()->route('bills.index')
            ->with('success', 'Recurring bill updated successfully.');
    }

    public function destroy(RecurringExpense $bill): RedirectResponse
    {
        Gate::authorize('delete', $bill);

        $bill->delete();

        return redirect()->route('bills.index')
            ->with('success', 'Recurring bill deleted successfully.');
    }

    public function markPaid(Request $request, RecurringExpense $bill): RedirectResponse
    {
        Gate::authorize('update', $bill);

        $bill->update([
            'is_paid' => true,
            'last_paid_at' => now(),
        ]);

        $request->user()->transactions()->create([
            'amount' => $bill->amount,
            'type' => 'expense',
            'category' => $bill->category,
            'description' => $bill->name.' (recurring)',
            'date' => now()->toDateString(),
        ]);

        return redirect()->route('bills.index')
            ->with('success', 'Bill marked as paid.');
    }
}
