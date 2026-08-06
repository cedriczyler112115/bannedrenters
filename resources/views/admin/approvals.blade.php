<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account approvals &middot; Mindanao Banned Car Renters</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7f5] text-slate-900">
    <nav class="border-b border-slate-200/80 bg-white px-4 py-3 sm:px-6">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 text-[#102922]">
                <span class="grid size-10 place-items-center rounded-lg bg-[#d9f67a]"><x-car-icon class="size-7" /></span>
                <span class="hidden font-bold sm:inline">Mindanao Banned Car Renters</span>
            </a>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">Admin</span>
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-[#102922]">Back to registry</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-10">
        <div class="mb-7">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-[#38836d]">Administration</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.03em] text-[#102922]">Account approvals</h1>
            <p class="mt-2 text-sm text-slate-500">Review newly registered and Google-created accounts before granting registry access.</p>
        </div>

        @if (session('status'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        <div class="grid gap-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-800">Pending approvals</h2>
                    <p class="mt-1 text-sm text-slate-500">Accounts waiting for review.</p>
                </div>

                @forelse ($pendingUsers as $user)
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            @if ($user->avatar)
                                <img src="{{ $user->avatar }}" alt="" class="size-11 rounded-full object-cover ring-2 ring-emerald-100">
                            @else
                                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-[#153d32] text-xs font-bold text-white">{{ $user->initials() }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">{{ $user->name }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $user->contact_number ?: 'Contact number not set' }}</p>
                                <p class="mt-1 text-xs text-slate-400">Registered {{ $user->created_at->diffForHumans() }}{{ $user->google_id ? ' via Google' : '' }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.approvals.approve', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#153d32] px-5 text-sm font-semibold text-white transition hover:bg-[#102922] sm:w-auto">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                Approve account
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg>
                        </span>
                        <h2 class="mt-4 font-semibold text-slate-700">No accounts waiting</h2>
                        <p class="mt-1 text-sm text-slate-400">All registered accounts have been reviewed.</p>
                    </div>
                @endforelse

                @if ($pendingUsers->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $pendingUsers->links() }}</div>
                @endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-800">Approved accounts</h2>
                    <p class="mt-1 text-sm text-slate-500">Recently approved users.</p>
                </div>

                @forelse ($approvedUsers as $user)
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            @if ($user->avatar)
                                <img src="{{ $user->avatar }}" alt="" class="size-11 rounded-full object-cover ring-2 ring-emerald-100">
                            @else
                                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ $user->initials() }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">{{ $user->name }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $user->contact_number ?: 'Contact number not set' }}</p>
                                <p class="mt-1 text-xs text-slate-400">Approved {{ $user->approved_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-50 text-slate-400">
                            <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg>
                        </span>
                        <h2 class="mt-4 font-semibold text-slate-700">No approved accounts yet</h2>
                        <p class="mt-1 text-sm text-slate-400">Approved users will appear here after review.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </main>
</body>
</html>
