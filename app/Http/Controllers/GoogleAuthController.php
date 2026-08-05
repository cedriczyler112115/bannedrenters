<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in has not been configured yet.',
            ]);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_oauth_state');

        if (! $expectedState || ! hash_equals($expectedState, (string) $request->query('state'))) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in could not be verified. Please try again.']);
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in was cancelled.']);
        }

        try {
            $token = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $request->string('code')->toString(),
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
            ])->throw()->json();

            $profile = Http::withToken($token['access_token'])
                ->get('https://openidconnect.googleapis.com/v1/userinfo')
                ->throw()
                ->json();

            if (empty($profile['email']) || empty($profile['sub']) || empty($profile['email_verified'])) {
                throw new \RuntimeException('Google did not return a verified email address.');
            }

            $user = User::where('google_id', $profile['sub'])
                ->orWhere('email', $profile['email'])
                ->first();

            if ($user) {
                $user->update([
                    'google_id' => $profile['sub'],
                    'avatar' => $profile['picture'] ?? $user->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $profile['name'] ?? Str::before($profile['email'], '@'),
                    'email' => $profile['email'],
                    'google_id' => $profile['sub'],
                    'avatar' => $profile['picture'] ?? null,
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            if (blank($user->contact_number)) {
                return redirect()->route('contact.edit');
            }

            if (! $user->isApproved()) {
                return redirect()->route('approval.pending');
            }

            return redirect()->route('dashboard');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'google' => 'We could not sign you in with Google. Please try again.',
            ]);
        }
    }
}
