<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $webhookSecret) {
            Log::error('Stripe webhook secret not configured');

            return response('Webhook secret not configured', 500);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object);
        }

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $userId = $session->metadata->user_id ?? null;
        $months = (int) ($session->metadata->months ?? 1);

        if (! $userId) {
            Log::error('Stripe webhook: missing user_id in metadata');

            return;
        }

        $user = User::find($userId);

        if (! $user) {
            Log::error('Stripe webhook: user not found', ['user_id' => $userId]);

            return;
        }

        $user->subscription()
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'premium',
            'status' => 'active',
            'payment_method' => 'stripe',
            'stripe_customer_id' => $session->customer,
            'stripe_subscription_id' => $session->subscription ?? $session->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonths($months),
        ]);

        $user->profile?->update(['plan' => 'premium']);
    }
}
