<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · BannedRenters</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8f6] text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
        <section class="w-full max-w-[500px] auth-enter">
            <a href="{{ route('login') }}" class="mx-auto mb-7 flex w-fit items-center gap-3 text-[#102922]">
                <span class="grid size-12 place-items-center rounded-xl bg-[#d9f67a] shadow-sm">
                    <x-car-icon class="size-8" />
                </span>
                <span class="hidden font-bold tracking-tight sm:inline"><span class="text-[#38836d]">Mindanao</span> Banned <span class="text-[#38836d]">Car</span> Renters</span>
            </a>

            <div class="rounded-3xl border border-slate-200/80 bg-white px-6 py-8 shadow-xl shadow-slate-900/5 sm:px-10 sm:py-10">
                @yield('content')

                <div class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">
                    @yield('top-link')
                </div>
            </div>

            <div class="mt-7 flex justify-center gap-6 text-xs text-slate-400">
                <a href="#" class="transition hover:text-slate-700">Privacy</a>
                <a href="#" class="transition hover:text-slate-700">Terms</a>
                <a href="#" class="transition hover:text-slate-700">Help</a>
            </div>
        </section>
    </main>
</body>
</html>
