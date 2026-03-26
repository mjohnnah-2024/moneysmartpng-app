<?php

namespace App\Policies;

use App\Models\Squad;
use App\Models\User;

class SquadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Squad $squad): bool
    {
        return $squad->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        $maxSquads = $user->activePlan() === 'premium' ? 10 : 2;

        return $user->createdSquads()->count() < $maxSquads;
    }

    public function update(User $user, Squad $squad): bool
    {
        return $squad->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    public function delete(User $user, Squad $squad): bool
    {
        return $squad->creator_id === $user->id;
    }
}
