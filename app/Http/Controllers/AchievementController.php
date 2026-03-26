<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AchievementController extends Controller
{
    public function __invoke(Request $request, StreakService $streakService): Response
    {
        $user = $request->user();

        // Check for any new achievements
        $streakService->checkAchievements($user);

        return Inertia::render('achievements', [
            'badges' => $streakService->getBadgesForUser($user),
            'totalPoints' => $streakService->getTotalPoints($user),
            'streaks' => $user->streaks()->get()->map(fn ($s) => [
                'type' => $s->type,
                'current_count' => $s->current_count,
                'longest_count' => $s->longest_count,
                'last_recorded_at' => $s->last_recorded_at?->toIso8601String(),
            ])->values()->all(),
        ]);
    }
}
