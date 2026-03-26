<?php

use App\Http\Controllers\Admin\AiUsageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\ManualPaymentController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\Settings\LanguageController;
use App\Http\Controllers\Settings\ReferralController;
use App\Http\Controllers\Settings\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\CheckPlanGate;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::middleware(EnsureProfileComplete::class)->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::resource('transactions', TransactionController::class)->except(['show', 'store']);
        Route::post('transactions', [TransactionController::class, 'store'])
            ->name('transactions.store')
            ->middleware(CheckPlanGate::class.':transactions');

        Route::resource('budgets', BudgetController::class)->except(['show', 'store']);
        Route::post('budgets', [BudgetController::class, 'store'])
            ->name('budgets.store')
            ->middleware(CheckPlanGate::class.':budgets');

        Route::resource('goals', GoalController::class)->except(['show', 'store']);
        Route::post('goals', [GoalController::class, 'store'])
            ->name('goals.store')
            ->middleware(CheckPlanGate::class.':goals');
        Route::post('goals/{goal}/contribute', [GoalController::class, 'contribute'])->name('goals.contribute');
        Route::get('insights', InsightsController::class)->name('insights');

        Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('chat', [ChatController::class, 'store'])->name('chat.store')->middleware([CheckPlanGate::class.':ai_chat', 'throttle:ai-chat']);
        Route::delete('chat', [ChatController::class, 'clear'])->name('chat.clear');

        Route::get('settings/referral', [ReferralController::class, 'index'])->name('referral.index');
        Route::get('settings/language', [LanguageController::class, 'edit'])->name('language.edit');
        Route::patch('settings/language', [LanguageController::class, 'update'])->name('language.update');
        Route::get('export/transactions', [ExportController::class, 'transactions'])->name('export.transactions');

        Route::get('premium', [PremiumController::class, 'show'])->name('premium.show');
        Route::post('premium/checkout', [PremiumController::class, 'createCheckoutSession'])->name('premium.checkout');
        Route::post('premium/mobile-money', [ManualPaymentController::class, 'store'])->name('premium.mobile-money');

        Route::get('settings/subscription', [SubscriptionController::class, 'edit'])->name('subscription.edit');
    });

    Route::middleware(IsAdmin::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/grant-premium', [UserController::class, 'grantPremium'])->name('users.grant-premium');
        Route::post('users/{user}/revoke-premium', [UserController::class, 'revokePremium'])->name('users.revoke-premium');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{manualPayment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('payments/{manualPayment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::get('ai-usage', AiUsageController::class)->name('ai-usage');
    });
});

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

require __DIR__.'/settings.php';
