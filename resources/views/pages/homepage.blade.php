@extends('layouts.guestLayouts')

@section('content')
<section class="grid items-center gap-8 lg:grid-cols-2">
    <div>
        <h1 class="text-4xl font-extrabold leading-tight">Platform Kompetisi dan Ujian Online</h1>
        <p class="mt-4 max-w-xl text-slate-300">
            Daftar ujian, kerjakan soal dengan autosave, dan review hasil lengkap beserta waktu pengerjaan Anda.
        </p>
        <div class="mt-6 flex gap-3">
            <a href="{{ route('register') }}" class="rounded bg-emerald-400 px-4 py-2 font-semibold text-slate-900">Mulai Sekarang</a>
            <a href="{{ route('login') }}" class="rounded border border-slate-500 px-4 py-2">Sudah Punya Akun</a>
        </div>
    </div>

    <div class="rounded-xl border border-slate-700 bg-slate-900/60 p-5 shadow-xl">
        <h2 class="mb-3 text-lg font-semibold">Ujian Tersedia</h2>
        <div class="space-y-3">
            @forelse ($upcomingExams as $exam)
                @php
                    $now = now();
                    $isNotStarted = $exam->starts_at && $now->lt($exam->starts_at);
                    $isEnded = $exam->ends_at && $now->gt($exam->ends_at);
                    $isRunning = ! $isNotStarted && ! $isEnded && $exam->is_published;

                    $statusLabel = 'Draft';
                    $statusClass = 'bg-slate-700 text-slate-200';

                    if ($isNotStarted && $exam->is_published) {
                        $statusLabel = 'Belum Mulai';
                        $statusClass = 'bg-amber-400/20 text-amber-300';
                    } elseif ($isRunning) {
                        $statusLabel = 'Sedang Berlangsung';
                        $statusClass = 'bg-emerald-400/20 text-emerald-300';
                    } elseif ($isEnded) {
                        $statusLabel = 'Selesai';
                        $statusClass = 'bg-rose-400/20 text-rose-300';
                    }
                @endphp

                <div class="rounded border border-slate-700 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="font-semibold">{{ $exam->title }}</div>
                        <span class="rounded px-2 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="text-sm text-slate-400">Durasi {{ $exam->duration_minutes }} menit</div>
                </div>
            @empty
                <div class="text-sm text-slate-400">Belum ada ujian aktif saat ini.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
