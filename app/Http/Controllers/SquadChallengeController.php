<?php

namespace App\Http\Controllers;

use App\Models\Squad;
use App\Models\SquadChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SquadChallengeController extends Controller
{
    public function store(Request $request, Squad $squad): RedirectResponse
    {
        Gate::authorize('update', $squad);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $squad->challenges()->create($validated);

        return redirect()->route('squads.show', $squad)
            ->with('success', 'Challenge created successfully.');
    }

    public function show(Request $request, Squad $squad, SquadChallenge $challenge): Response
    {
        Gate::authorize('view', $squad);

        $challenge->load(['progress.user.profile']);

        $leaderboard = $challenge->progress
            ->map(fn ($p) => [
                'user_name' => $p->user->profile?->full_name ?? $p->user->name,
                'percentage' => $challenge->target_amount > 0
                    ? round(((float) $p->current_amount / (float) $challenge->target_amount) * 100, 1)
                    : 0,
            ])
            ->sortByDesc('percentage')
            ->values()
            ->all();

        $userProgress = $challenge->progress()
            ->where('user_id', $request->user()->id)
            ->first();

        return Inertia::render('squads/challenge', [
            'squad' => [
                'id' => $squad->id,
                'name' => $squad->name,
            ],
            'challenge' => [
                'id' => $challenge->id,
                'name' => $challenge->name,
                'target_amount' => (float) $challenge->target_amount,
                'starts_at' => $challenge->starts_at->format('Y-m-d'),
                'ends_at' => $challenge->ends_at->format('Y-m-d'),
                'is_active' => $challenge->is_active,
            ],
            'leaderboard' => $leaderboard,
            'userProgress' => $userProgress ? [
                'current_amount' => (float) $userProgress->current_amount,
                'percentage' => $challenge->target_amount > 0
                    ? round(((float) $userProgress->current_amount / (float) $challenge->target_amount) * 100, 1)
                    : 0,
            ] : null,
        ]);
    }

    public function updateProgress(Request $request, Squad $squad, SquadChallenge $challenge): RedirectResponse
    {
        Gate::authorize('view', $squad);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $challenge->progress()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['current_amount' => $validated['amount']],
        );

        return redirect()->route('squads.challenges.show', [$squad, $challenge])
            ->with('success', 'Progress updated.');
    }
}
