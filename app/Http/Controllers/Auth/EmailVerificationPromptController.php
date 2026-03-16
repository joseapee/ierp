<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return view('auth.verify-email');
        }

        $user = $request->user();

        if ($user->tenant_id === null) {
            return redirect()->intended(route('setup'));
        }

        $tenant = $user->tenant;

        if ($tenant && $tenant->onboarding_completed_at === null) {
            return redirect()->intended(route('onboarding'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
