<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('onboarding', [
            'user' => $request->user(),
        ]);
    }

    public function store(StoreProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $request->user()->profile()->create([
            ...$data,
            'referral_code' => $this->generateUniqueReferralCode(),
        ]);

        // Credit the referrer with 30 premium days
        if (! empty($data['referred_by'])) {
            Profile::where('referral_code', $data['referred_by'])
                ->increment('premium_days_earned', 30);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to MoneySmart PNG!');
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Profile::where('referral_code', $code)->exists());

        return $code;
    }
}
