<?php

namespace App\Policies;

use App\Models\UsageTracking;
use App\Models\User;

class UsageTrackingPolicy
{
    public function view(User $user, UsageTracking $usageTracking): bool
    {
        return $user->id === $usageTracking->user_id;
    }
}
