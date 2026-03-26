<?php

namespace App\Http\Middleware;

use App\Models\UsageTracking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanGate
{
    /** @var array<string, array{limit: int, period: string}> */
    private const FREE_LIMITS = [
        'transactions' => ['limit' => 50, 'period' => 'month'],
        'goals' => ['limit' => 2, 'period' => 'lifetime'],
        'budgets' => ['limit' => 3, 'period' => 'month'],
        'ai_chat' => ['limit' => 20, 'period' => 'month'],
    ];

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        $plan = $user?->activePlan() ?? 'free';

        if ($plan === 'premium') {
            return $next($request);
        }

        $config = self::FREE_LIMITS[$feature] ?? null;

        if (! $config) {
            return $next($request);
        }

        $count = $this->getCurrentCount($user->id, $feature, $config['period']);

        if ($count >= $config['limit']) {
            $featureLabels = [
                'transactions' => 'transactions',
                'goals' => 'active goals',
                'budgets' => 'budget categories',
                'ai_chat' => 'AI messages',
            ];

            $label = $featureLabels[$feature] ?? $feature;

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "You have reached your free plan limit of {$config['limit']} {$label}. Upgrade to premium for unlimited access.",
                    'upgrade' => true,
                ], 429);
            }

            return back()->with('error', "You have reached your free plan limit of {$config['limit']} {$label}. Upgrade to premium for unlimited access.");
        }

        return $next($request);
    }

    private function getCurrentCount(int $userId, string $feature, string $period): int
    {
        if ($period === 'lifetime') {
            return $this->getLifetimeCount($userId, $feature);
        }

        $tracking = UsageTracking::where('user_id', $userId)
            ->where('feature', $feature)
            ->where('period', now()->format('Y-m'))
            ->first();

        return $tracking?->count ?? 0;
    }

    private function getLifetimeCount(int $userId, string $feature): int
    {
        if ($feature === 'goals') {
            return \App\Models\Goal::where('user_id', $userId)
                ->where('status', 'active')
                ->count();
        }

        return 0;
    }
}
