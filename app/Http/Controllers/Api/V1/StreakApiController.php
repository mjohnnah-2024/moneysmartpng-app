<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreakApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $streaks = $request->user()->streaks()->get()->map(fn ($s) => [
            'type' => $s->type,
            'current_count' => $s->current_count,
            'longest_count' => $s->longest_count,
            'last_recorded_at' => $s->last_recorded_at?->toISOString(),
        ]);

        return response()->json($streaks);
    }
}
