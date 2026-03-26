<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->profile;

        $referralCount = Profile::where('referred_by', $profile->referral_code)->count();

        return Inertia::render('settings/referral', [
            'referralCode' => $profile->referral_code,
            'referralCount' => $referralCount,
            'premiumDaysEarned' => $profile->premium_days_earned,
            'referralLink' => url('/register?ref='.$profile->referral_code),
        ]);
    }
}
