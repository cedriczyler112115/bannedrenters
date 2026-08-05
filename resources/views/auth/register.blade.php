@extends('layouts.auth')

@section('title', 'Create your account')

@section('top-link')
    <span class="hidden sm:inline">Already a member?</span> <a href="{{ route('login') }}" class="sm:ml-1 font-semibold text-[#186a53] hover:text-[#102922]">Sign in</a>
@endsection

@section('content')
    <div class="mb-7">
        <p class="mb-3 text-xs font-bold uppercase tracking-[.2em] text-[#38836d]">Get started</p>
        <h2 class="text-4xl font-semibold tracking-[-.035em] text-[#102922]">Create your account</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Join the community and make more informed rental decisions.</p>
    </div>

    @if ($errors->has('google'))
        <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('google') }}</div>
    @endif

    <a href="{{ route('google.redirect') }}" class="flex h-12 w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-px hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-100">
        <svg class="size-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.39-.18-2.05H12v3.87h5.38a4.6 4.6 0 0 1-2 3.02v2.51h3.24c1.89-1.74 2.98-4.31 2.98-7.35Z"/><path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.62-2.42l-3.24-2.51c-.9.6-2.05.96-3.38.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.59A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.39 13.9A6 6 0 0 1 6.08 12c0-.66.11-1.3.31-1.9V7.51H3.04A10 10 0 0 0 2 12c0 1.61.39 3.14 1.04 4.49l3.35-2.59Z"/><path fill="#EA4335" d="M12 5.97c1.47 0 2.79.51 3.83 1.5l2.87-2.88A9.63 9.63 0 0 0 12 2a10 10 0 0 0-8.96 5.51l3.35 2.59C7.18 7.73 9.39 5.97 12 5.97Z"/></svg>
        Sign up with Google
    </a>

    <div class="my-6 flex items-center gap-4 text-[11px] font-semibold uppercase tracking-[.16em] text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span><span>or use email</span><span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus placeholder="Your full name" class="auth-input @error('name') !border-red-400 @enderror">
            @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required placeholder="you@example.com" class="auth-input @error('email') !border-red-400 @enderror">
            @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="contact_number" class="mb-2 block text-sm font-semibold text-slate-700">Contact number</label>
            <input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number') }}" autocomplete="tel" inputmode="tel" required placeholder="e.g. 0917 123 4567" class="auth-input @error('contact_number') !border-red-400 @enderror">
            @error('contact_number')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required placeholder="Min. 8 characters" class="auth-input @error('password') !border-red-400 @enderror">
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Repeat password" class="auth-input">
            </div>
        </div>
        @error('password')<p class="-mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        <label class="flex cursor-pointer items-start gap-3 text-xs leading-5 text-slate-500">
            <input type="checkbox" name="terms" value="1" required class="mt-0.5 size-4 shrink-0 rounded border-slate-300 accent-[#1b6b53]">
            <span>I agree to the <a href="#" class="font-semibold text-[#26765f]">Terms of Service</a> and <a href="#" class="font-semibold text-[#26765f]">Privacy Policy</a>.</span>
        </label>
        @error('terms')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        <button type="submit" class="auth-submit">Create account <span aria-hidden="true">→</span></button>
    </form>
@endsection
