<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\UsageTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->user()->transactions()->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->value());
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->value());
        }

        return Inertia::render('transactions/index', [
            'transactions' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'type', 'category']),
            'categories' => Transaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('transactions/create', [
            'categories' => [
                'expense' => Transaction::EXPENSE_CATEGORIES,
                'income' => Transaction::INCOME_CATEGORIES,
            ],
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $request->user()->transactions()->create($request->validated());

        UsageTracking::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'feature' => 'transactions',
                'period' => now()->format('Y-m'),
            ],
            ['count' => 0],
        )->increment('count');

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction added successfully.');
    }

    public function edit(Transaction $transaction): Response
    {
        Gate::authorize('update', $transaction);

        return Inertia::render('transactions/edit', [
            'transaction' => $transaction,
            'categories' => [
                'expense' => Transaction::EXPENSE_CATEGORIES,
                'income' => Transaction::INCOME_CATEGORIES,
            ],
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        $transaction->update($request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
