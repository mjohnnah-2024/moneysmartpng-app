<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ManualPayment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $now = Carbon::now();

        $totalUsers = User::count();
        $newToday = User::whereDate('created_at', $now->toDateString())->count();
        $activePremium = Subscription::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->count();
        $conversionRate = $totalUsers > 0 ? round(($activePremium / $totalUsers) * 100, 1) : 0;
        $aiMessages = ChatMessage::count();

        $stripeRevenue = Subscription::where('payment_method', 'stripe')
            ->where('status', 'active')
            ->count();
        $mobileRevenue = ManualPayment::where('status', 'approved')->sum('amount');
        $estimatedRevenue = ($stripeRevenue * 9.99) + (float) $mobileRevenue;

        // Daily signups for the last 30 days
        $isSqlite = DB::getDriverName() === 'sqlite';
        $dateExpr = $isSqlite ? "strftime('%Y-%m-%d', created_at)" : 'DATE(created_at)';

        $dailySignups = User::where('created_at', '>=', $now->copy()->subDays(30)->startOfDay())
            ->select(DB::raw("$dateExpr as date"), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'count' => $row->count]);

        // Weekly premium upgrades for the last 8 weeks
        $weekExpr = $isSqlite ? "strftime('%Y-%W', created_at)" : 'YEARWEEK(created_at, 1)';

        $weeklyUpgrades = Subscription::where('created_at', '>=', $now->copy()->subWeeks(8)->startOfWeek())
            ->where('status', 'active')
            ->select(DB::raw("$weekExpr as week"), DB::raw('COUNT(*) as count'))
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(fn ($row) => ['week' => $row->week, 'count' => $row->count]);

        // Recent activity feed (latest 20 events)
        $recentUsers = User::latest()->take(10)->get(['id', 'name', 'email', 'created_at'])
            ->map(fn ($u) => [
                'type' => 'signup',
                'description' => "{$u->name} signed up",
                'timestamp' => $u->created_at->toISOString(),
            ]);

        $recentSubscriptions = Subscription::with('user:id,name')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($s) => [
                'type' => 'subscription',
                'description' => ($s->user->name ?? 'User') . " subscribed via {$s->payment_method}",
                'timestamp' => $s->created_at->toISOString(),
            ]);

        $recentPayments = ManualPayment::with('user:id,name')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'type' => 'payment',
                'description' => ($p->user->name ?? 'User') . ' submitted mobile money payment',
                'timestamp' => $p->created_at->toISOString(),
            ]);

        $recentActivity = $recentUsers
            ->merge($recentSubscriptions)
            ->merge($recentPayments)
            ->sortByDesc('timestamp')
            ->take(20)
            ->values();

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'newToday' => $newToday,
                'activePremium' => $activePremium,
                'conversionRate' => $conversionRate,
                'aiMessages' => $aiMessages,
                'estimatedRevenue' => round($estimatedRevenue, 2),
            ],
            'dailySignups' => $dailySignups,
            'weeklyUpgrades' => $weeklyUpgrades,
            'recentActivity' => $recentActivity,
        ]);
    }
}
