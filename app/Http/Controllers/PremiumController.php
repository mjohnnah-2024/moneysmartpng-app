<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PremiumController extends Controller
{
    /** @var array<string, array{months: int, price: float, label: string}> */
    public const PRICING = [
        'monthly' => ['months' => 1, 'price' => 9.99, 'label' => '1 Month'],
        'quarterly' => ['months' => 3, 'price' => 24.99, 'label' => '3 Months'],
        'biannual' => ['months' => 6, 'price' => 44.99, 'label' => '6 Months'],
        'annual' => ['months' => 12, 'price' => 79.99, 'label' => '12 Months'],
    ];

    public function show(Request $request): Response
    {
        $user = $request->user();
        $subscription = $user->subscription;

        return Inertia::render('premium', [
            'currentPlan' => $user->activePlan(),
            'subscription' => $subscription?->only('plan', 'status', 'payment_method', 'starts_at', 'ends_at'),
            'pricing' => self::PRICING,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function createCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', 'in:monthly,quarterly,biannual,annual'],
        ]);

        $stripeSecret = config('services.stripe.secret');

        if (! $stripeSecret) {
            return response()->json(['error' => 'Stripe is not configured. Please contact support.'], 503);
        }

        $user = $request->user();
        $pricing = self::PRICING[$request->input('plan')];

        $stripe = new \Stripe\StripeClient($stripeSecret);

        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $user->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'pgk',
                    'product_data' => [
                        'name' => "MoneySmart Premium - {$pricing['label']}",
                    ],
                    'unit_amount' => (int) ($pricing['price'] * 100),
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'user_id' => $user->id,
                'plan_key' => $request->input('plan'),
                'months' => $pricing['months'],
            ],
            'success_url' => route('premium.show') . '?success=1',
            'cancel_url' => route('premium.show') . '?cancelled=1',
        ]);

        return response()->json(['id' => $session->id]);
    }
}
