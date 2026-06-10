@extends('layouts.panel')

@section('content')
<div class="mb-5 rounded bg-white p-5 shadow">
    <h1 class="text-2xl font-bold">Review Hasil Ujian</h1>
    <div class="mt-2 text-sm text-slate-600">
        Ujian: {{ $attempt->exam->title }}
    </div>
    <div class="mt-1 text-sm text-slate-600">
        Durasi Pengerjaan: {{ gmdate('H:i:s', $attempt->duration_seconds) }}
    </div>
    <div class="mt-1 text-sm font-semibold text-indigo-700">
        Total Skor: {{ $attempt->total_score }}
    </div>
</div>

<div class="space-y-4">
    @foreach ($attempt->exam->questions as $question)
        @php
            $answer = $answersByQuestion->get($question->id);
            $selectedOption = $answer?->answer_option;
            $selectedOptionLabel = $selectedOption && isset($question->options[$selectedOption])
                ? $question->options[$selectedOption]
                : null;
            $correctOption = $question->correct_answer;
            $correctOptionLabel = $correctOption && isset($question->options[$correctOption])
                ? $question->options[$correctOption]
                : null;
        @endphp

        <div class="rounded bg-white p-5 shadow">
            <h2 class="font-semibold">Soal {{ $question->question_number }}</h2>
            <p class="mt-1">{{ $question->question_text }}</p>
                @if ($question->image_path)
                    <img src="{{ asset('storage/' . $question->image_path) }}" alt="Gambar soal" class="mt-3 max-h-60 object-contain">
                @endif
            @if ($question->options)
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    <div class="font-medium text-slate-800">Daftar Opsi:</div>
                    @foreach ($question->options as $key => $label)
                        <div class="rounded border px-3 py-2 {{ $selectedOption === $key ? 'border-indigo-300 bg-indigo-50' : 'border-slate-200' }} {{ $correctOption === $key ? 'ring-1 ring-emerald-400' : '' }}">
                            <span class="font-semibold">{{ $key }}.</span>
                            <span>{{ $label }}</span>
                            @if ($selectedOption === $key)
                                <span class="ml-2 text-xs font-semibold text-indigo-700">Dipilih user</span>
                            @endif
                            @if ($correctOption === $key)
                                <span class="ml-2 text-xs font-semibold text-emerald-700">Jawaban benar</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-3 text-sm text-slate-700">
                <div>
                    Jawaban Anda:
                    @if ($selectedOption)
                        <span class="font-medium">{{ $selectedOption }}</span>
                        @if ($selectedOptionLabel)
                            - {{ $selectedOptionLabel }}
                        @endif
                    @else
                        -
                    @endif
                </div>
                <div class="mt-1">
                    Jawaban Benar:
                    @if ($correctOption)
                        <span class="font-medium">{{ $correctOption }}</span>
                        @if ($correctOptionLabel)
                            - {{ $correctOptionLabel }}
                        @endif
                    @else
                        -
                    @endif
                </div>
                <div>Status: {{ is_null($answer?->is_correct) ? '-' : ($answer->is_correct ? 'Benar' : 'Salah') }}</div>
            </div>
        </div>
    @endforeach
</div>
@endsection
