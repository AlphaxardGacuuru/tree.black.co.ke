<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SocialiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteController extends Controller
{
    public function __construct(private SocialiteService $socialiteService) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return $this->failedLoginRedirect();
        }

        if (! filled($googleUser->getEmail())) {
            return $this->failedLoginRedirect('Your Google account did not provide an email address.');
        }

        $user = $this->socialiteService->resolveUser($googleUser);

        auth()->login($user, true);
        $request->session()->regenerate();

        $this->socialiteService->syncFamilyContext($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Signed in with Google successfully.']);

        return redirect()->intended(config('fortify.home'));
    }

    private function failedLoginRedirect(?string $message = null): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->withErrors([
                'socialite' => $message ?? 'Unable to sign in with Google. Please try again.',
            ]);
    }
}
