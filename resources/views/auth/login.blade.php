@extends('layouts.auth')

@section('title', 'Welcome back')

@section('top-link')
    <span class="hidden sm:inline">New here?</span> <a href="{{ route('register') }}" class="sm:ml-1 font-semibold text-[#186a53] hover:text-[#102922]">Create account</a>
@endsection

@section('content')
    <div class="mb-4">
        <p class="mb-3 text-xs font-bold uppercase tracking-[.2em] text-[#38836d]">Welcome back</p>
        <h2 class="text-4xl font-semibold tracking-[-.035em] text-[#102922]">Sign in to your account</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Enter your details to access your renter network.</p>
    </div>

    @if ($errors->has('google'))
        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('google') }}</div>
    @endif

    <a href="{{ route('google.redirect') }}" class="group flex h-12 w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-px hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-100">
        <svg class="size-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.39-.18-2.05H12v3.87h5.38a4.6 4.6 0 0 1-2 3.02v2.51h3.24c1.89-1.74 2.98-4.31 2.98-7.35Z"/><path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.62-2.42l-3.24-2.51c-.9.6-2.05.96-3.38.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.59A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.39 13.9A6 6 0 0 1 6.08 12c0-.66.11-1.3.31-1.9V7.51H3.04A10 10 0 0 0 2 12c0 1.61.39 3.14 1.04 4.49l3.35-2.59Z"/><path fill="#EA4335" d="M12 5.97c1.47 0 2.79.51 3.83 1.5l2.87-2.88A9.63 9.63 0 0 0 12 2a10 10 0 0 0-8.96 5.51l3.35 2.59C7.18 7.73 9.39 5.97 12 5.97Z"/></svg>
        Continue with Google
    </a>

    <div class="my-7 flex items-center gap-4 text-[11px] font-semibold uppercase tracking-[.16em] text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span><span>or use email</span><span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="you@example.com" class="auth-input @error('email') !border-red-400 @enderror">
            @error('email')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
            </div>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password" class="auth-input pr-12">
                <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 grid w-12 place-items-center text-slate-400 transition hover:text-slate-700" aria-label="Show password">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                </button>
            </div>
        </div>
        <div class="flex items-start gap-2.5 rounded-xl bg-emerald-50 px-3.5 py-3 text-xs leading-5 text-emerald-800">
            <svg viewBox="0 0 24 24" class="mt-0.5 size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
            <span>You’ll stay signed in on this device until you sign out or clear your browser data.</span>
        </div>
        <button type="submit" class="auth-submit">Sign in <span aria-hidden="true">→</span></button>
    </form>
@endsection
