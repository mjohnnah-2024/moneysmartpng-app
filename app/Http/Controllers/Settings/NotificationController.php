<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public const NOTIFICATION_TYPES = [
        'daily_limit_warning' => 'Daily Limit Warning',
        'category_reduction' => 'Budget Overspend',
        'savings_opportunity' => 'Savings Opportunity',
        'goal_pace_warning' => 'Goal Pace Warning',
        'income_reminder' => 'Income Reminder',
        'bill_reminder' => 'Bill Reminder',
        'streak_encouragement' => 'Streak Encouragement',
    ];

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $preferences = $user->notificationPreferences->keyBy('type');

        $notificationSettings = collect(self::NOTIFICATION_TYPES)->map(fn ($label, $type) => [
            'type' => $type,
            'label' => $label,
            'in_app' => $preferences[$type]->in_app ?? true,
            'push' => $preferences[$type]->push ?? false,
            'enabled' => $preferences[$type]->enabled ?? true,
        ])->values()->all();

        return Inertia::render('settings/notifications', [
            'notifications' => $notificationSettings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notifications' => ['required', 'array'],
            'notifications.*.type' => ['required', 'string'],
            'notifications.*.in_app' => ['required', 'boolean'],
            'notifications.*.push' => ['required', 'boolean'],
            'notifications.*.enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        foreach ($validated['notifications'] as $pref) {
            $user->notificationPreferences()->updateOrCreate(
                ['type' => $pref['type']],
                [
                    'in_app' => $pref['in_app'],
                    'push' => $pref['push'],
                    'enabled' => $pref['enabled'],
                ]
            );
        }

        return redirect()->route('notifications.edit')
            ->with('success', 'Notification preferences updated.');
    }
}
