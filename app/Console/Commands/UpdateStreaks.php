<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SafeToSpendService;
use App\Services\StreakService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:update-streaks')]
#[Description('Update daily streaks for all users and check achievements')]
class UpdateStreaks extends Command
{
    public function handle(StreakService $streakService, SafeToSpendService $safeToSpendService): int
    {
        $users = User::whereHas('profile')->with('profile')->get();
        $updated = 0;

        foreach ($users as $user) {
            $safeToSpend = $safeToSpendService->calculate($user);

            // If user stayed within safe-to-spend, update under_budget streak
            if ($safeToSpend['status'] !== 'red') {
                $streakService->updateStreak($user, 'under_budget');
            } else {
                $streakService->breakStreak($user, 'under_budget');
            }

            // Check and grant achievements
            $streakService->checkAchievements($user);
            $updated++;
        }

        $this->info("Updated streaks for {$updated} users.");

        return self::SUCCESS;
    }
}
