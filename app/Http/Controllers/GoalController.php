<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContributeGoalRequest;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filter = $request->input('status', 'active');

        $query = $user->goals()->orderByDesc('created_at');

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $goals = $query->get()->map(fn (Goal $goal) => [
            'id' => $goal->id,
            'name' => $goal->name,
            'target_amount' => (float) $goal->target_amount,
            'current_amount' => (float) $goal->current_amount,
            'deadline' => $goal->deadline?->format('Y-m-d'),
            'status' => $goal->status,
            'percentage' => $goal->target_amount > 0
                ? round(((float) $goal->current_amount / (float) $goal->target_amount) * 100, 1)
                : 0,
            'created_at' => $goal->created_at->toISOString(),
        ]);

        $summary = [
            'activeCount' => $user->goals()->where('status', 'active')->count(),
            'completedCount' => $user->goals()->where('status', 'completed')->count(),
            'totalSaved' => (float) $user->goals()->where('status', 'active')->sum('current_amount'),
            'totalTarget' => (float) $user->goals()->where('status', 'active')->sum('target_amount'),
        ];

        return Inertia::render('goals/index', [
            'goals' => $goals,
            'filter' => $filter,
            'summary' => $summary,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('goals/create');
    }

    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $request->user()->goals()->create($request->validated());

        return redirect()->route('goals.index')
            ->with('success', 'Goal created successfully.');
    }

    public function edit(Goal $goal): Response
    {
        Gate::authorize('update', $goal);

        return Inertia::render('goals/edit', [
            'goal' => $goal,
        ]);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): RedirectResponse
    {
        Gate::authorize('update', $goal);

        $goal->update($request->validated());

        return redirect()->route('goals.index')
            ->with('success', 'Goal updated successfully.');
    }

    public function contribute(ContributeGoalRequest $request, Goal $goal): RedirectResponse
    {
        Gate::authorize('update', $goal);

        $goal->increment('current_amount', $request->validated('amount'));

        if ((float) $goal->fresh()->current_amount >= (float) $goal->target_amount) {
            $goal->update(['status' => 'completed']);

            return redirect()->route('goals.index')
                ->with('success', 'Congratulations! You\'ve reached your goal!');
        }

        return redirect()->route('goals.index')
            ->with('success', 'Contribution added successfully.');
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        Gate::authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Goal deleted successfully.');
    }
}
