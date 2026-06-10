<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="panel-theme" x-data="{ open: false, userMenuOpen: false }">
    <div class="panel-shell">
        <div
            x-show="open"
            x-transition.opacity
            @click="open = false"
            class="fixed inset-0 z-30 bg-slate-950/40 md:hidden"
            style="display: none;"
        ></div>

        <aside
            class="panel-sidebar fixed inset-y-0 left-0 z-40 w-72 transform transition-transform md:static md:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="panel-brand-wrap">
                <div class="panel-brand">Web Kompetisi</div>
            </div>

            <div class="panel-user-card">
                <div class="panel-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div>
                    <div class="panel-user-name">{{ Auth::user()->name }}</div>
                    <!-- <div class="panel-user-role">{{ ucfirst(Auth::user()->role) }}</div> -->
                </div>
            </div>

            <nav class="space-y-1 p-3 text-sm">
                <a href="{{ route('dashboard') }}"
                   class="panel-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>

                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.exams.index') }}"
                       class="panel-nav-item {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
                        Manajemen Ujian
                    </a>
                    <a href="{{ route('admin.exams.create') }}"
                       class="panel-nav-item {{ request()->routeIs('admin.exams.create') ? 'active' : '' }}">
                        Buat Ujian
                    </a>
                    <a href="{{ route('admin.extensions.index') }}"
                       class="panel-nav-item {{ request()->routeIs('admin.extensions.*') ? 'active' : '' }}">
                        Tambah Waktu User
                    </a>
                @endif

                @if (Auth::user()->role === 'user')
                    <a href="{{ route('user.exams.index') }}"
                       class="panel-nav-item {{ request()->routeIs('user.exams.*') ? 'active' : '' }}">
                        Daftar Ujian
                    </a>
                @endif
            </nav>

            <!-- <div class="mt-auto border-t border-slate-200/80 p-3 text-xs text-slate-400">
                Panel pengguna aktif
            </div> -->
        </aside>

        <div class="panel-main">
            <header class="panel-topbar sticky top-0 z-20">
                <div class="panel-topbar-inner">
                    <div class="flex items-center gap-3">
                        <button @click="open = true" class="panel-menu-button md:hidden">Menu</button>
                        <!-- <div class="panel-search-wrap">
                            <input type="text" placeholder="Search projects" class="panel-search">
                        </div> -->
                    </div>
                    <div class="panel-topbar-right">
                        <div class="text-xs text-slate-500">{{ now()->format('d M Y H:i') }}</div>

                        <div class="relative" @click.outside="userMenuOpen = false">
                            <button
                                type="button"
                                @click="userMenuOpen = !userMenuOpen"
                                class="ml-2 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                x-show="userMenuOpen"
                                x-transition.opacity
                                style="display: none;"
                                class="absolute right-0 z-50 mt-2 w-44 rounded-xl border border-slate-200 bg-white p-1 shadow-lg"
                            >
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                    @click="userMenuOpen = false"
                                >
                                    Profile
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="block w-full rounded-lg px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50"
                                    >
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="panel-content p-4 md:p-6" data-panel-content>
                <div class="mb-5 rounded-2xl bg-gradient-to-r from-fuchsia-100/80 via-indigo-100/90 to-teal-100/80 p-4 shadow-sm">
                    <h1 class="text-base font-semibold text-slate-700 md:text-lg">{{ $pageTitle ?? 'Dashboard' }}</h1>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
