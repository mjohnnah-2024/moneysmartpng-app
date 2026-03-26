<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RecommendationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationApiController extends Controller
{
    public function __invoke(Request $request, RecommendationsService $service): JsonResponse
    {
        return response()->json($service->generate($request->user()));
    }
}
