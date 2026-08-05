@extends('layouts.auth')

@section('title', 'Add your contact number')

@section('top-link')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="font-semibold text-[#186a53] hover:text-[#102922]">Sign out</button>
    </form>
@endsection

@section('content')
    <div class="mb-7 text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-100 text-[#176047]">
            <svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.8 2.1Z"/>
            </svg>
        </span>
        <p class="mt-6 text-xs font-bold uppercase tracking-[.2em] text-[#38836d]">Complete your profile</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-[-.035em] text-[#102922]">Add your contact number</h1>
        <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-500">A contact number is required before you can continue to search the banned renters.</p>
    </div>

    <form method="POST" action="{{ route('contact.update') }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label for="contact_number" class="mb-2 block text-sm font-semibold text-slate-700">Contact number</label>
            <input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number') }}" autocomplete="tel" inputmode="tel" required autofocus placeholder="e.g. 0917 123 4567" class="auth-input @error('contact_number') !border-red-400 @enderror">
            @error('contact_number')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="auth-submit">Save and continue <span aria-hidden="true">&rarr;</span></button>
    </form>
@endsection
