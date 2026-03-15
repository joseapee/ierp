<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /** @var array<int, string> */
    protected array $providers = ['google', 'apple'];

    /**
     * Redirect to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers, true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth callback from the provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers, true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception) {
            return redirect()->route('login')
                ->with('error', 'Unable to authenticate with '.ucfirst($provider).'. Please try again.');
        }

        $user = User::query()
            ->where('social_provider', $provider)
            ->where('social_id', $socialUser->getId())
            ->first();

        if ($user) {
            Auth::login($user, remember: true);

            return $user->tenant_id
                ? redirect()->intended(route('dashboard'))
                : redirect()->route('setup');
        }

        $user = User::query()
            ->where('email', $socialUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'social_provider' => $provider,
                'social_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            Auth::login($user, remember: true);

            return $user->tenant_id
                ? redirect()->intended(route('dashboard'))
                : redirect()->route('setup');
        }

        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => null,
            'social_provider' => $provider,
            'social_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
        ]);

        Auth::login($user, remember: true);

        return redirect()->route('setup');
    }
}
