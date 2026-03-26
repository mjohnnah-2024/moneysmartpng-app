<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LanguageController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/language', [
            'currentLanguage' => $request->user()->profile->preferred_language,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_language' => ['required', Rule::in(['en', 'tpi'])],
        ]);

        $request->user()->profile->update($validated);

        return back()->with('success', 'Language updated successfully.');
    }
}
