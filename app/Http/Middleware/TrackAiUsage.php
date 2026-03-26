<?php

namespace App\Http\Middleware;

use App\Models\UsageTracking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAiUsage
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $plan = $user?->profile?->plan ?? 'free';

        if ($plan !== 'premium') {
            $usage = UsageTracking::where('user_id', $user->id)
                ->where('feature', 'ai_chat')
                ->where('period', now()->format('Y-m'))
                ->first();

            if ($usage && $usage->count >= 20) {
                return response()->json([
                    'error' => 'You have reached your free plan limit of 20 AI messages this month. Upgrade to premium for unlimited access.',
                ], 429);
            }
        }

        return $next($request);
    }
}
