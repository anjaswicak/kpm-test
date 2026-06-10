<x-guest-layout>
    <div class="grid w-full max-w-4xl overflow-hidden rounded-2xl bg-[#1d1d2f] shadow-[0_30px_60px_-30px_rgba(0,0,0,0.7)] md:grid-cols-2">
        <div class="relative hidden md:block">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(129,97,255,0.4),transparent_50%),radial-gradient(circle_at_80%_85%,rgba(71,209,255,0.2),transparent_40%),linear-gradient(160deg,#3d2f78,#2a2759_70%)]"></div>
            <div class="relative z-10 flex h-full flex-col justify-between p-8 text-white">
                <a href="{{ route('landing') }}" class="inline-flex w-fit rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs">Back to website</a>
                <div>
                    <p class="text-2xl font-semibold leading-tight">Demo Web Kompetisi</p>
                    <!-- <p class="mt-2 text-sm text-white/75">Capturing moments, creating memories.</p> -->
                </div>
            </div>
        </div>

        <div class="p-6 text-white md:p-8">
            <h1 class="text-3xl font-semibold">Welcome back</h1>
            <p class="mt-1 text-sm text-white/70">Login ke akun Anda untuk melanjutkan ujian.</p>

            <x-auth-session-status class="mt-4 rounded border border-emerald-300/40 bg-emerald-500/20 px-3 py-2 text-sm text-emerald-100" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="email" class="text-xs font-medium text-white/80">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="mt-1 w-full rounded-md border border-white/15 bg-[#2a2a3f] px-3 py-2 text-sm text-white placeholder:text-white/40 focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400">
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-300" />
                </div>

                <div>
                    <label for="password" class="text-xs font-medium text-white/80">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="mt-1 w-full rounded-md border border-white/15 bg-[#2a2a3f] px-3 py-2 text-sm text-white placeholder:text-white/40 focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400">
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-300" />
                </div>

                <div class="flex items-center justify-between text-xs text-white/70">
                    <label for="remember_me" class="inline-flex items-center gap-2">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-white/30 bg-transparent text-violet-500 focus:ring-violet-400">
                        Ingat saya
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="hover:text-white">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="w-full rounded-md bg-violet-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-400">
                    Log in
                </button>
            </form>

            <p class="mt-4 text-center text-xs text-white/60">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-violet-300 hover:text-violet-200">Daftar sekarang</a>
            </p>
        </div>
    </div>
</x-guest-layout>
