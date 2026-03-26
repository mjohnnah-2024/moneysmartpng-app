<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementApiController extends Controller
{
    public function __invoke(Request $request, StreakService $service): JsonResponse
    {
        return response()->json([
            'badges' => $service->getBadgesForUser($request->user()),
            'totalPoints' => $service->getTotalPoints($request->user()),
        ]);
    }
}
