<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:expire')]
#[Description('Expire active subscriptions that have passed their end date')]
class ExpireSubscriptions extends Command
{
    public function handle(): int
    {
        $expired = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);
            $subscription->user?->profile?->update(['plan' => 'free']);
            $count++;
        }

        $this->info("Expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}
