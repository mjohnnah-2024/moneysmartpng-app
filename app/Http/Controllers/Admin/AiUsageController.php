<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AiUsageController extends Controller
{
    private const DAILY_ALERT_THRESHOLD = 1000;

    private const ESTIMATED_COST_PER_MESSAGE = 0.002;

    public function __invoke(Request $request): Response
    {
        $now = Carbon::now();

        $totalMessages = ChatMessage::count();
        $todayMessages = ChatMessage::whereDate('created_at', $now->toDateString())->count();
        $estimatedCost = round($totalMessages * self::ESTIMATED_COST_PER_MESSAGE, 2);
        $todayCost = round($todayMessages * self::ESTIMATED_COST_PER_MESSAGE, 2);
        $dailyAlert = $todayMessages >= self::DAILY_ALERT_THRESHOLD;

        // Hourly AI message volume for the last 24 hours
        $isSqlite = DB::getDriverName() === 'sqlite';
        $hourExpr = $isSqlite ? "strftime('%Y-%m-%d %H:00', created_at)" : 'DATE_FORMAT(created_at, "%Y-%m-%d %H:00")';

        $hourlyVolume = ChatMessage::where('created_at', '>=', $now->copy()->subHours(24))
            ->select(
                DB::raw("$hourExpr as hour"),
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn ($row) => ['hour' => $row->hour, 'count' => $row->count]);

        // Daily totals for the last 30 days
        $dateExpr = $isSqlite ? "strftime('%Y-%m-%d', created_at)" : 'DATE(created_at)';

        $dailyTotals = ChatMessage::where('created_at', '>=', $now->copy()->subDays(30)->startOfDay())
            ->select(DB::raw("$dateExpr as date"), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'count' => $row->count]);

        // Top users by AI usage
        $topUsers = ChatMessage::select('user_id', DB::raw('COUNT(*) as message_count'))
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderByDesc('message_count')
            ->take(10)
            ->get()
            ->map(fn ($row) => [
                'user_id' => $row->user_id,
                'name' => $row->user?->name ?? 'Deleted User',
                'email' => $row->user?->email ?? '',
                'message_count' => $row->message_count,
            ]);

        return Inertia::render('admin/ai-usage', [
            'stats' => [
                'totalMessages' => $totalMessages,
                'todayMessages' => $todayMessages,
                'estimatedCost' => $estimatedCost,
                'todayCost' => $todayCost,
                'dailyAlert' => $dailyAlert,
                'alertThreshold' => self::DAILY_ALERT_THRESHOLD,
            ],
            'hourlyVolume' => $hourlyVolume,
            'dailyTotals' => $dailyTotals,
            'topUsers' => $topUsers,
        ]);
    }
}
