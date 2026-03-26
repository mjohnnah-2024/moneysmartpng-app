<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with(['profile', 'subscription']);

        if ($search = $request->input('search')) {
            $search = mb_substr((string) $search, 0, 100);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortField = in_array($request->input('sort'), ['name', 'email', 'created_at']) ? $request->input('sort') : 'created_at';
        $sortDir = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $users = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        return Inertia::render('admin/users', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort' => $sortField,
                'direction' => $sortDir,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/user-form');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $user->forceFill([
            'is_admin' => $request->boolean('is_admin'),
            'email_verified_at' => now(),
        ])->save();

        return redirect()->route('admin.users.show', $user)->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user): Response
    {
        $user->load(['profile', 'subscription', 'manualPayments' => fn ($q) => $q->latest()->take(5)]);

        return Inertia::render('admin/user-detail', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/user-form', [
            'user' => $user->only('id', 'name', 'email', 'is_admin'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        $user->forceFill(['is_admin' => $request->boolean('is_admin')])->save();

        return redirect()->route('admin.users.show', $user)->with('success', "User {$user->name} updated successfully.");
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        $status = $user->is_admin ? 'granted' : 'revoked';

        return back()->with('success', "Admin access {$status} for {$user->name}.");
    }

    public function grantPremium(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'months' => ['required', 'integer', 'in:1,3,6,12'],
        ]);

        // Expire existing active subscriptions
        $user->subscription()
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'premium',
            'status' => 'active',
            'payment_method' => 'mobile_money',
            'starts_at' => now(),
            'ends_at' => now()->addMonths($request->integer('months')),
        ]);

        $user->profile?->update(['plan' => 'premium']);

        return back()->with('success', "Premium granted to {$user->name} for {$request->input('months')} month(s).");
    }

    public function revokePremium(User $user): RedirectResponse
    {
        $user->subscription()
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $user->profile?->update(['plan' => 'free']);

        return back()->with('success', "Premium revoked for {$user->name}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is_admin) {
            return back()->with('error', 'Cannot delete admin users.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->transactions()->delete();
        $user->budgets()->delete();
        $user->goals()->delete();
        $user->chatMessages()->delete();
        $user->usageTracking()->delete();
        $user->manualPayments()->delete();
        $user->recurringExpenses()->delete();
        $user->notificationPreferences()->delete();
        $user->streaks()->delete();
        $user->achievements()->delete();
        $user->squadMemberships()->delete();
        Subscription::where('user_id', $user->id)->delete();
        Profile::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
