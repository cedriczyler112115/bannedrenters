<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Banned renter registry · Mindanao Banned Renters</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7f5] text-slate-900">
    <nav class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur sm:px-6">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 text-[#102922]">
                <span class="grid size-10 place-items-center rounded-lg bg-[#d9f67a]">
                    <x-car-icon class="size-7" />
                </span>
                <span class="hidden font-bold tracking-tight sm:inline"><span class="text-[#38836d]">Mindanao</span> Banned <span class="text-[#38836d]">Car</span> Renters</span>
                <span class="font-bold tracking-tight sm:hidden">BANNED RENTERS</span>
            </a>

            <div class="flex items-center gap-3">
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.approvals.index') }}" class="relative rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 sm:text-sm">
                        Approvals
                        @if ($pendingApprovalCount > 0)
                            <span class="absolute -right-2 -top-2 grid min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-5 text-white">{{ $pendingApprovalCount }}</span>
                        @endif
                    </a>
                @endif
                <div class="flex items-center gap-2.5 border-r border-slate-200 pr-3">
                    @if (auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="" class="size-9 rounded-full object-cover ring-2 ring-emerald-100">
                    @else
                        <span class="grid size-9 place-items-center rounded-full bg-[#153d32] text-xs font-bold text-white">{{ auth()->user()->initials() }}</span>
                    @endif
                    <div class="hidden leading-tight sm:block">
                        <p class="max-w-40 truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="max-w-40 truncate text-[11px] text-slate-400">{{ auth()->user()->email }}</p>
                        <p class="max-w-40 truncate text-[11px] text-slate-400">{{ auth()->user()->contact_number }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 sm:px-4 sm:text-sm">Sign out</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:py-10">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="mt-2 text-3xl font-semibold tracking-[-.03em] text-[#102922]">List of Banned Car Renters in Mindanao</h1>
                <p class="mt-2 text-sm text-slate-500">Review and document renter records shared by verified members.</p>
            </div>
            <details data-record-modal class="group relative sm:w-auto" @if($errors->any() && old('_form') === 'create') open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-center gap-2 rounded-xl bg-[#153d32] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-950/10 transition hover:bg-[#102922]">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    Add banned renter
                </summary>
                <div data-modal-backdrop class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[2px] sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:z-30 sm:mt-3 sm:block sm:w-[440px] sm:bg-transparent sm:p-0 sm:backdrop-blur-none">
                    <div role="dialog" aria-modal="true" aria-labelledby="new-record-title" class="max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:max-h-none sm:max-w-none sm:shadow-xl">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 id="new-record-title" class="font-semibold text-[#102922]">Create new banned renter record</h2>
                                <p class="mt-1 text-xs text-slate-500">The license image is optional and may be up to 5 MB.</p>
                            </div>
                            <button type="button" data-modal-close class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800" aria-label="Close add renter form">
                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('banned.store') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_form" value="create">
                            <div>
                                <label for="fullname" class="mb-1.5 block text-xs font-semibold text-slate-700">Full name</label>
                                <input id="fullname" name="fullname" value="{{ old('fullname') }}" maxlength="150" required class="auth-input" placeholder="Renter's complete name">
                                @error('fullname')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="address" class="mb-1.5 block text-xs font-semibold text-slate-700">Address</label>
                                <input id="address" name="address" value="{{ old('address') }}" maxlength="255" required class="auth-input" placeholder="Complete residential address">
                                @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="license" class="mb-1.5 block text-xs font-semibold text-slate-700">License image <span class="font-normal text-slate-400">(optional)</span></label>
                                <input id="license" name="license" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-500 file:mr-3 file:border-0 file:bg-[#e9f6ef] file:px-4 file:py-3 file:font-semibold file:text-[#176047] hover:file:bg-[#dcefe5]">
                                @error('license')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="description" class="mb-1.5 block text-xs font-semibold text-slate-700">Why it is Banned?</label>
                                <textarea id="description" name="description" rows="7" maxlength="5000" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#38836d] focus:ring-4 focus:ring-emerald-100" placeholder="Describe the reason...">{{ old('description') }}</textarea>
                                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="auth-submit">Save record <span aria-hidden="true">&rarr;</span></button>
                        </form>
                    </div>
                </div>
            </details>
        </div>

        @if (session('status'))
            <div role="status" class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                <span class="grid size-6 place-items-center rounded-full bg-emerald-600 text-white">&#10003;</span>
                {{ session('status') }}
            </div>
        @endif

        <section>
            <form method="GET" action="{{ route('dashboard') }}" data-loading-form class="mx-auto mb-8 flex max-w-2xl items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                <div class="relative min-w-0 flex-1">
                    <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <label for="fullname-filter" class="sr-only">Find by full name</label>
                    <input id="fullname-filter" name="fullname" value="{{ $filters['fullname'] ?? '' }}" maxlength="150" class="h-11 w-full rounded-xl border-0 bg-transparent pl-11 pr-3 text-sm outline-none placeholder:text-slate-400 focus:ring-0" placeholder="Enter the renter's full name">
                </div>
                @if (filled($filters['fullname'] ?? null))
                    <a href="{{ route('dashboard') }}" data-clear-search class="grid size-11 shrink-0 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Clear search">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </a>
                @endif
                <button class="flex h-11 shrink-0 items-center gap-2 rounded-xl bg-[#153d32] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#102922] focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    Find
                </button>
            </form>

            <div data-registry-skeleton class="hidden" aria-hidden="true">
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach (range(1, 6) as $item)
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="aspect-[16/9] animate-pulse bg-slate-200"></div>
                            <div class="space-y-4 p-5">
                                <div class="h-3 w-20 animate-pulse rounded bg-slate-200"></div>
                                <div class="h-6 w-2/3 animate-pulse rounded bg-slate-200"></div>
                                <div class="space-y-2"><div class="h-3 w-full animate-pulse rounded bg-slate-100"></div><div class="h-3 w-5/6 animate-pulse rounded bg-slate-100"></div><div class="h-3 w-3/4 animate-pulse rounded bg-slate-100"></div></div>
                                <div class="flex items-center gap-3 border-t border-slate-100 pt-4"><div class="size-9 animate-pulse rounded-full bg-slate-200"></div><div class="space-y-2"><div class="h-3 w-28 animate-pulse rounded bg-slate-200"></div><div class="h-2.5 w-20 animate-pulse rounded bg-slate-100"></div></div></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div data-registry-results>
                @if (filled($filters['fullname'] ?? null))
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <p class="text-sm text-slate-500"><span class="font-semibold text-slate-800">{{ $records->total() }}</span> {{ Str::plural('result', $records->total()) }} for “{{ $filters['fullname'] }}”</p>
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($records as $record)
                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-emerald-200 hover:shadow-lg">
                            @if ($record->license)
                                <button type="button" data-license-preview data-image-src="{{ Storage::disk('public')->url($record->license) }}" data-image-alt="License for {{ $record->fullname }}" class="relative block aspect-[16/9] w-full overflow-hidden bg-slate-100 text-left focus:outline-none focus:ring-4 focus:ring-inset focus:ring-emerald-300" aria-label="View full license photo for {{ $record->fullname }}">
                                    <img src="{{ Storage::disk('public')->url($record->license) }}" alt="License for {{ $record->fullname }}" class="size-full object-cover transition duration-300 group-hover:scale-[1.02]">
                                    <span class="absolute right-3 top-3 rounded-full border border-white/70 bg-white/90 px-2.5 py-1 text-[10px] font-bold tracking-wide text-slate-600 shadow-sm backdrop-blur">LICENSE IMAGE</span>
                                </button>
                            @else
                                <div class="flex aspect-[16/9] w-full flex-col items-center justify-center bg-slate-100 text-slate-400">
                                    <svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 16 5-5 4 4 2-2 7 6"/><circle cx="16.5" cy="9.5" r="1.5"/></svg>
                                    <span class="mt-2 text-xs font-semibold">No license image provided</span>
                                </div>
                            @endif

                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 shrink-0">
                                        <p class="text-[10px] font-bold uppercase tracking-[.1em] text-[#38836d]">Record #{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <p class="mt-1 max-w-32 truncate text-[9px] text-slate-500" title="{{ $record->source ?: 'Not provided' }}"><span class="font-bold uppercase tracking-[.08em] text-slate-400">Source:</span> <b>{{ $record->source ?: 'Not provided' }}</b></p>
                                    </div>
                                    <div class="flex min-w-0 items-start divide-x divide-slate-200 text-right">
                                        <div class="min-w-0 px-2.5 first:pl-0">
                                            <p class="text-[7px] font-bold uppercase tracking-[.1em] text-slate-400">Added by</p>
                                            <p class="mt-0.5 max-w-24 truncate text-[9px] font-semibold text-slate-600" title="{{ $record->creator->email }}">{{ $record->creator->name }}</p>
                                        </div>
                                        <div class="min-w-0 px-2.5">
                                            <p class="text-[7px] font-bold uppercase tracking-[.1em] text-slate-400">Mobile number</p>
                                            <p class="mt-0.5 max-w-24 truncate text-[9px] font-semibold text-slate-600" title="{{ $record->creator->contact_number ?: 'Not set' }}">{{ $record->creator->contact_number ?: 'Not set' }}</p>
                                        </div>
                                        <div class="shrink-0 pl-2.5">
                                            <p class="text-[7px] font-bold uppercase tracking-[.1em] text-slate-400">Date created</p>
                                            <p class="mt-0.5 text-[9px] font-semibold text-slate-600">{{ $record->date_created->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <h2 class="mt-2 text-xl font-semibold tracking-[-.02em] text-[#102922]">{{ $record->fullname }}</h2>

                                <div class="mt-3 border-l-2 border-emerald-200 pl-3">
                                    <p class="text-[9px] font-bold uppercase tracking-[.12em] text-slate-400">Address</p>
                                    <p class="mt-0.5 text-sm leading-5 text-slate-600">{{ $record->address ?: 'Address not provided' }}</p>
                                </div>

                                <div class="my-5 rounded-xl bg-slate-50 p-4">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-[10px] font-bold uppercase tracking-[.12em] text-slate-400">Why it is Banned?</p>
                                        <div class="flex items-center gap-1">
                                            @can('update', $record)
                                                <details data-record-modal class="relative" @if($errors->any() && old('_form') === "update-{$record->id}") open @endif>
                                                    <summary class="inline-flex cursor-pointer list-none items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-semibold text-[#26765f] transition hover:bg-emerald-100 hover:text-[#153d32] focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                                        <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                                        Edit
                                                    </summary>
                                                    <div data-modal-backdrop class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4 text-left backdrop-blur-[2px]">
                                                        <div role="dialog" aria-modal="true" aria-labelledby="edit-record-title-{{ $record->id }}" class="max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
                                                            <div class="mb-5 flex items-start justify-between gap-4">
                                                                <div>
                                                                    <h2 id="edit-record-title-{{ $record->id }}" class="text-base font-semibold normal-case tracking-normal text-[#102922]">Edit banned renter record</h2>
                                                                    <p class="mt-1 text-xs font-normal normal-case tracking-normal text-slate-500">Leave the license image empty to keep the current photo.</p>
                                                                </div>
                                                                <button type="button" data-modal-close class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800" aria-label="Close edit renter form">
                                                                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                                                </button>
                                                            </div>
                                                            <form method="POST" action="{{ route('banned.update', $record) }}" enctype="multipart/form-data" class="space-y-4">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="_form" value="update-{{ $record->id }}">
                                                                <div>
                                                                    <label for="edit-fullname-{{ $record->id }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Full name</label>
                                                                    <input id="edit-fullname-{{ $record->id }}" name="fullname" value="{{ old('_form') === "update-{$record->id}" ? old('fullname') : $record->fullname }}" maxlength="150" required class="auth-input">
                                                                    @if(old('_form') === "update-{$record->id}") @error('fullname')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
                                                                </div>
                                                                <div>
                                                                    <label for="edit-address-{{ $record->id }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Address</label>
                                                                    <input id="edit-address-{{ $record->id }}" name="address" value="{{ old('_form') === "update-{$record->id}" ? old('address') : $record->address }}" maxlength="255" required class="auth-input">
                                                                    @if(old('_form') === "update-{$record->id}") @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
                                                                </div>
                                                                <div>
                                                                    <label for="edit-license-{{ $record->id }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Replace license image <span class="font-normal text-slate-400">(optional)</span></label>
                                                                    <input id="edit-license-{{ $record->id }}" name="license" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-500 file:mr-3 file:border-0 file:bg-[#e9f6ef] file:px-4 file:py-3 file:font-semibold file:text-[#176047] hover:file:bg-[#dcefe5]">
                                                                    @if(old('_form') === "update-{$record->id}") @error('license')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
                                                                </div>
                                                                <div>
                                                                    <label for="edit-description-{{ $record->id }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Why it is Banned?</label>
                                                                    <textarea id="edit-description-{{ $record->id }}" name="description" rows="7" maxlength="5000" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#38836d] focus:ring-4 focus:ring-emerald-100">{{ old('_form') === "update-{$record->id}" ? old('description') : $record->description }}</textarea>
                                                                    @if(old('_form') === "update-{$record->id}") @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
                                                                </div>
                                                                <button type="submit" class="auth-submit">Save changes <span aria-hidden="true">&rarr;</span></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </details>
                                            @endcan

                                            @can('delete', $record)
                                                <form method="POST" action="{{ route('banned.destroy', $record) }}" data-delete-record data-record-name="{{ $record->fullname }}">
                                                @csrf
                                                @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-semibold text-red-600 transition hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                                                        <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                    <p class="whitespace-pre-line text-sm leading-6 text-slate-600">{{ $record->description }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center">
                            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 8 5 5M22 8l-5 5"/></svg>
                            </span>
                            <h3 class="mt-4 font-semibold text-slate-700">No renter found</h3>
                            <p class="mt-1 text-sm text-slate-400">Try checking the spelling or enter a different full name.</p>
                        </div>
                    @endforelse
                </div>

                @if ($records->hasPages() || $records->total())
                    <div data-registry-pagination class="mt-8 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                        <div class="mb-3 text-xs text-slate-400 sm:hidden">Showing {{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} of {{ $records->total() }}</div>
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </section>

        <div data-license-lightbox class="fixed inset-0 z-[70] hidden items-center justify-center p-4 sm:p-8" role="dialog" aria-modal="true" aria-label="Full license photo">
            <button type="button" data-lightbox-backdrop class="absolute inset-0 cursor-default bg-slate-950/85 backdrop-blur-sm" aria-label="Close full license photo"></button>
            <div class="relative z-10 flex max-h-full max-w-6xl items-center justify-center">
                <img data-lightbox-image src="" alt="" class="max-h-[calc(100vh-2rem)] max-w-full rounded-xl object-contain shadow-2xl sm:max-h-[calc(100vh-4rem)]">
                <button type="button" data-lightbox-close class="absolute -right-2 -top-2 grid size-10 place-items-center rounded-full bg-white text-slate-700 shadow-xl transition hover:scale-105 hover:bg-slate-100 sm:-right-4 sm:-top-4" aria-label="Close full license photo">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </main>
</body>
</html>
