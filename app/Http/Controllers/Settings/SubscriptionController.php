<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UsageTracking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $subscription = $user->subscription;

        $currentMonth = now()->format('Y-m');
        $usage = UsageTracking::where('user_id', $user->id)
            ->where('period', $currentMonth)
            ->pluck('count', 'feature')
            ->toArray();

        return Inertia::render('settings/subscription', [
            'currentPlan' => $user->activePlan(),
            'subscription' => $subscription?->only('id', 'plan', 'status', 'payment_method', 'starts_at', 'ends_at'),
            'usage' => [
                'transactions' => $usage['transactions'] ?? 0,
                'budgets' => $usage['budgets'] ?? 0,
                'goals' => $user->goals()->where('status', 'active')->count(),
                'ai_chat' => $usage['ai_chat'] ?? 0,
            ],
            'limits' => [
                'transactions' => 50,
                'budgets' => 3,
                'goals' => 2,
                'ai_chat' => 20,
            ],
        ]);
    }
}
