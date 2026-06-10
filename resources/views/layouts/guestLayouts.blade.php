<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-800 text-slate-100">
    <header class="border-b border-slate-700/60">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <div class="text-lg font-bold tracking-wide">Mini Exam Platform</div>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('login') }}" class="rounded border border-slate-500 px-3 py-1 hover:bg-slate-700">Login</a>
                <a href="{{ route('register') }}" class="rounded bg-emerald-500 px-3 py-1 font-semibold text-slate-900 hover:bg-emerald-400">Register</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
