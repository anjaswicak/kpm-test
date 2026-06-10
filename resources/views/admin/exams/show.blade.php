@extends('layouts.panel')

@section('content')
<div class="mb-6 rounded bg-white p-5 shadow">
    <h1 class="text-2xl font-bold">{{ $exam->title }}</h1>
    <p class="mt-2 text-slate-600">{{ $exam->description }}</p>
    <div class="mt-3 text-sm text-slate-500">
        Durasi: {{ $exam->duration_minutes }} menit | Soal: {{ $exam->questions->count() }}
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded bg-white p-5 shadow">
        <h2 class="mb-4 text-lg font-semibold">Tambah Soal</h2>
        <form method="POST" action="{{ route('admin.questions.store', $exam) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <textarea name="question_text" rows="3" class="w-full rounded border px-3 py-2" placeholder="Tulis soal" required></textarea>
            <input type="file" name="image" class="w-full rounded border px-3 py-2">

            <div class="grid grid-cols-2 gap-3">
                <input name="option_a" class="rounded border px-3 py-2" placeholder="Opsi A">
                <input name="option_b" class="rounded border px-3 py-2" placeholder="Opsi B">
                <input name="option_c" class="rounded border px-3 py-2" placeholder="Opsi C">
                <input name="option_d" class="rounded border px-3 py-2" placeholder="Opsi D">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <select name="correct_answer" class="rounded border px-3 py-2">
                    <option value="">Jawaban benar</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
                <input type="number" min="1" name="points" value="1" class="rounded border px-3 py-2" placeholder="Poin" required>
            </div>

            <button class="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">Tambah Soal</button>
        </form>
    </div>

    <div class="rounded bg-white p-5 shadow">
        <h2 class="mb-4 text-lg font-semibold">Daftar Soal</h2>
        <div class="max-h-[500px] space-y-3 overflow-auto pr-1">
            @forelse ($exam->questions as $question)
                <div class="rounded border p-3">
                    <div class="font-semibold">No {{ $question->question_number }}</div>
                    <div class="mt-1 text-sm text-slate-700">{{ $question->question_text }}</div>
                    @if ($question->options)
                        <ul class="mt-2 text-xs text-slate-600">
                            @foreach ($question->options as $key => $label)
                                <li>{{ $key }}. {{ $label }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada soal.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
