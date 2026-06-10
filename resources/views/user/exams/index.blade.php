@extends('layouts.panel')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Daftar Ujian</h1>

<div class="grid gap-4 md:grid-cols-2">
    @forelse ($exams as $exam)
        @php
            $attempt = $attemptsByExamId->get($exam->id);
            $extraSeconds = (int) ($extensionSecondsByExamId[$exam->id] ?? 0);
            $attemptEndsAt = $attempt?->started_at
                ? $attempt->started_at->copy()->addSeconds(($exam->duration_minutes * 60) + $extraSeconds)
                : null;
            $isSubmitted = filled($attempt?->submitted_at);
            $isExpired = $attemptEndsAt && now()->greaterThan($attemptEndsAt);
            $isExamExpired = filled($exam->ends_at) && now()->greaterThan($exam->ends_at);
            $canResumeByExtension = $attempt && $isSubmitted && $extraSeconds > 0 && $attemptEndsAt && now()->lessThan($attemptEndsAt);
        @endphp

        <div class="rounded bg-white p-5 shadow">
            <h2 class="text-lg font-semibold">{{ $exam->title }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ $exam->description }}</p>
            <div class="mt-2 text-sm text-slate-500">
                Durasi {{ $exam->duration_minutes }} menit | Soal {{ $exam->questions_count }}
            </div>

            <div class="mt-4">
                @if ($canResumeByExtension)
                    <form method="POST" action="{{ route('user.attempts.start', $exam) }}">
                        @csrf
                        <button class="rounded bg-emerald-600 px-4 py-2 text-white" type="submit">Lanjutkan Ujian</button>
                    </form>
                @elseif ($isSubmitted)
                    <a
                        class="inline-flex rounded bg-slate-700 px-4 py-2 text-white"
                        href="{{ $attempt ? route('user.attempts.review', $attempt) : route('user.exams.index') }}"
                    >
                        Lihat Hasil Ujian
                    </a>
                @elseif ($isExamExpired)
                    <button class="cursor-not-allowed rounded bg-slate-400 px-4 py-2 text-white" type="button" disabled>
                        Expired
                    </button>
                @elseif (in_array($exam->id, $registeredExamIds))
                    @if ($isExpired)
                        <a
                            class="inline-flex rounded bg-slate-700 px-4 py-2 text-white"
                            href="{{ $attempt ? route('user.attempts.review', $attempt) : route('user.exams.index') }}"
                        >
                            Ujian Sudah Expired
                        </a>
                    @else
                        <form method="POST" action="{{ route('user.attempts.start', $exam) }}">
                            @csrf
                            <button class="rounded bg-emerald-600 px-4 py-2 text-white" type="submit">Kerjakan Ujian</button>
                        </form>
                    @endif
                @else
                    <form method="POST" action="{{ route('user.exams.register', $exam) }}">
                        @csrf
                        <button class="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">Daftar Ujian</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded bg-white p-6 text-slate-500 shadow">Belum ada ujian tersedia.</div>
    @endforelse
</div>
@endsection
