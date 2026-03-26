<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinSquadRequest;
use App\Http\Requests\StoreSquadRequest;
use App\Models\Squad;
use App\Models\SquadMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SquadController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $squads = $user->squadMemberships()
            ->with(['squad' => fn ($q) => $q->withCount('members', 'challenges')])
            ->get()
            ->map(fn (SquadMember $membership) => [
                'id' => $membership->squad->id,
                'name' => $membership->squad->name,
                'description' => $membership->squad->description,
                'invite_code' => $membership->squad->invite_code,
                'max_members' => $membership->squad->max_members,
                'is_active' => $membership->squad->is_active,
                'member_count' => $membership->squad->members_count,
                'challenge_count' => $membership->squad->challenges_count,
                'role' => $membership->role,
            ]);

        return Inertia::render('squads/index', [
            'squads' => $squads,
            'canCreate' => Gate::allows('create', Squad::class),
        ]);
    }

    public function store(StoreSquadRequest $request): RedirectResponse
    {
        Gate::authorize('create', Squad::class);

        $user = $request->user();

        $squad = Squad::create([
            ...$request->validated(),
            'creator_id' => $user->id,
            'invite_code' => strtoupper(Str::random(8)),
        ]);

        $squad->members()->create([
            'user_id' => $user->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return redirect()->route('squads.show', $squad)
            ->with('success', 'Squad created successfully.');
    }

    public function show(Request $request, Squad $squad): Response
    {
        Gate::authorize('view', $squad);

        $squad->loadCount('members', 'challenges');

        $members = $squad->members()->with('user.profile')->get()->map(fn (SquadMember $m) => [
            'id' => $m->id,
            'name' => $m->user->profile?->full_name ?? $m->user->name,
            'role' => $m->role,
            'joined_at' => $m->joined_at->toISOString(),
        ]);

        $challenges = $squad->challenges()
            ->with(['progress.user.profile'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($challenge) => [
                'id' => $challenge->id,
                'name' => $challenge->name,
                'target_amount' => (float) $challenge->target_amount,
                'starts_at' => $challenge->starts_at->format('Y-m-d'),
                'ends_at' => $challenge->ends_at->format('Y-m-d'),
                'is_active' => $challenge->is_active,
                'progress' => $challenge->progress->map(fn ($p) => [
                    'user_name' => $p->user->profile?->full_name ?? $p->user->name,
                    'percentage' => $challenge->target_amount > 0
                        ? round(((float) $p->current_amount / (float) $challenge->target_amount) * 100, 1)
                        : 0,
                ])->sortByDesc('percentage')->values()->all(),
            ]);

        $userRole = $squad->members()
            ->where('user_id', $request->user()->id)
            ->value('role');

        return Inertia::render('squads/show', [
            'squad' => [
                'id' => $squad->id,
                'name' => $squad->name,
                'description' => $squad->description,
                'invite_code' => $squad->invite_code,
                'max_members' => $squad->max_members,
                'is_active' => $squad->is_active,
                'member_count' => $squad->members_count,
                'challenge_count' => $squad->challenges_count,
            ],
            'members' => $members,
            'challenges' => $challenges,
            'userRole' => $userRole,
        ]);
    }

    public function join(JoinSquadRequest $request): RedirectResponse
    {
        $squad = Squad::where('invite_code', strtoupper($request->validated('invite_code')))->first();

        if (! $squad) {
            return back()->with('error', 'Invalid invite code.');
        }

        if (! $squad->is_active) {
            return back()->with('error', 'This squad is no longer active.');
        }

        $user = $request->user();

        if ($squad->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('squads.show', $squad)
                ->with('error', 'You are already a member of this squad.');
        }

        if ($squad->members()->count() >= $squad->max_members) {
            return back()->with('error', 'This squad is full.');
        }

        $squad->members()->create([
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        return redirect()->route('squads.show', $squad)
            ->with('success', 'You have joined the squad!');
    }

    public function leave(Request $request, Squad $squad): RedirectResponse
    {
        $user = $request->user();

        if ($squad->creator_id === $user->id) {
            return back()->with('error', 'The squad creator cannot leave. Delete the squad instead.');
        }

        $squad->members()->where('user_id', $user->id)->delete();

        return redirect()->route('squads.index')
            ->with('success', 'You have left the squad.');
    }

    public function destroy(Squad $squad): RedirectResponse
    {
        Gate::authorize('delete', $squad);

        $squad->delete();

        return redirect()->route('squads.index')
            ->with('success', 'Squad deleted successfully.');
    }
}
