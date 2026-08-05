<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            return back()->withErrors([
                'email' => 'The email or password you entered is incorrect.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (blank($request->user()->contact_number)) {
            return redirect()->route('contact.edit');
        }

        if (! $request->user()->isApproved()) {
            return redirect()->route('approval.pending');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['required', 'string', 'min:7', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ], [
            'contact_number.regex' => 'Enter a valid contact number using digits, spaces, parentheses, +, or -.',
        ]);

        $user = DB::transaction(fn (): User => User::query()->create([
            'name' => trim($validated['name']),
            'email' => Str::lower($validated['email']),
            'contact_number' => trim($validated['contact_number']),
            'password' => $validated['password'],
        ]));

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()
            ->route('approval.pending')
            ->with('status', 'Your account has been created and is waiting for administrator approval.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
