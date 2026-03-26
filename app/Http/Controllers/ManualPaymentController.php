<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManualPaymentRequest;
use Illuminate\Http\RedirectResponse;

class ManualPaymentController extends Controller
{
    public function store(ManualPaymentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $months = $request->validated('months');

        $pricing = PremiumController::PRICING;
        $planKey = match ($months) {
            1 => 'monthly',
            3 => 'quarterly',
            6 => 'biannual',
            12 => 'annual',
            default => 'monthly',
        };

        $amount = $pricing[$planKey]['price'];

        $user->manualPayments()->create([
            'reference_number' => $request->validated('reference_number'),
            'phone' => $request->validated('phone'),
            'months' => $months,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your payment has been submitted for verification. You will be notified once approved.');
    }
}
