<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SafeToSpendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafeToSpendApiController extends Controller
{
    public function __invoke(Request $request, SafeToSpendService $service): JsonResponse
    {
        return response()->json($service->calculate($request->user()));
    }
}
