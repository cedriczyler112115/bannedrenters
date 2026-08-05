@extends('layouts.auth')

@section('title', 'Approval pending')

@section('top-link')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="font-semibold text-[#186a53] hover:text-[#102922]">Sign out</button>
    </form>
@endsection

@section('content')
    <div class="text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-amber-100 text-amber-700">
            <svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            </svg>
        </span>
        <p class="mt-6 text-xs font-bold uppercase tracking-[.2em] text-amber-600">Approval pending</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-[-.035em] text-[#102922]">Your account is being reviewed</h1>
        <p class="mx-auto mt-4 max-w-sm text-sm leading-6 text-slate-500">
            An administrator must approve your account before you can access the renter registry. You can return to this page later to check your status.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif


    </div>
@endsection
