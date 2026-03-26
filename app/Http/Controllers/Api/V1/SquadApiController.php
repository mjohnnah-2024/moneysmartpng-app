<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SquadMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SquadApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $squads = $request->user()->squadMemberships()
            ->with(['squad' => fn ($q) => $q->withCount('members', 'challenges')])
            ->get()
            ->map(fn (SquadMember $m) => [
                'id' => $m->squad->id,
                'name' => $m->squad->name,
                'description' => $m->squad->description,
                'member_count' => $m->squad->members_count,
                'challenge_count' => $m->squad->challenges_count,
                'role' => $m->role,
            ]);

        return response()->json($squads);
    }
}
