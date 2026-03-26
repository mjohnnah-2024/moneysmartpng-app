<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PremiumController;
use App\Models\ManualPayment;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = in_array($request->input('tab'), ['stripe', 'mobile_money']) ? $request->input('tab') : 'mobile_money';

        $stripePayments = Subscription::with('user:id,name,email')
            ->where('payment_method', 'stripe')
            ->latest()
            ->paginate(20, ['*'], 'stripe_page')
            ->withQueryString();

        $mobilePayments = ManualPayment::with('user:id,name,email')
            ->latest()
            ->paginate(20, ['*'], 'mobile_page')
            ->withQueryString();

        $pendingCount = ManualPayment::where('status', 'pending')->count();

        return Inertia::render('admin/payments', [
            'stripePayments' => $stripePayments,
            'mobilePayments' => $mobilePayments,
            'activeTab' => $tab,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function approve(Request $request, ManualPayment $manualPayment): RedirectResponse
    {
        if ($manualPayment->status !== 'pending') {
            return back()->with('error', 'This payment has already been processed.');
        }

        $manualPayment->update([
            'status' => 'approved',
            'admin_notes' => mb_substr((string) $request->input('admin_notes', ''), 0, 500),
        ]);

        $user = $manualPayment->user;

        // Expire old active subscriptions
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Look up the months from the pricing config
        $months = $manualPayment->months;

        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'premium',
            'status' => 'active',
            'payment_method' => 'mobile_money',
            'starts_at' => now(),
            'ends_at' => now()->addMonths($months),
        ]);

        $user->profile?->update(['plan' => 'premium']);

        return back()->with('success', "Payment approved. {$user->name} upgraded to Premium for {$months} month(s).");
    }

    public function reject(Request $request, ManualPayment $manualPayment): RedirectResponse
    {
        if ($manualPayment->status !== 'pending') {
            return back()->with('error', 'This payment has already been processed.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $manualPayment->update([
            'status' => 'rejected',
            'admin_notes' => mb_substr((string) $request->input('admin_notes'), 0, 500),
        ]);

        return back()->with('success', 'Payment rejected.');
    }
}
