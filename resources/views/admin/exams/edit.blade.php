@extends('layouts.panel')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Edit Ujian</h1>

<form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="space-y-4 rounded bg-white p-6 shadow">
    @csrf
    @method('PUT')

    <div>
        <label class="mb-1 block text-sm font-medium">Judul Ujian</label>
        <input
            name="title"
            value="{{ old('title', $exam->title) }}"
            class="w-full rounded border px-3 py-2"
            required
        >
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium">Deskripsi</label>
        <textarea name="description" class="w-full rounded border px-3 py-2" rows="3">{{ old('description', $exam->description) }}</textarea>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-medium">Durasi (menit)</label>
            <input
                type="number"
                min="1"
                name="duration_minutes"
                value="{{ old('duration_minutes', $exam->duration_minutes) }}"
                class="w-full rounded border px-3 py-2"
                required
            >
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Mulai</label>
            <input
                type="datetime-local"
                name="starts_at"
                value="{{ old('starts_at', optional($exam->starts_at)->format('Y-m-d\TH:i')) }}"
                class="w-full rounded border px-3 py-2"
            >
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Selesai</label>
            <input
                type="datetime-local"
                name="ends_at"
                value="{{ old('ends_at', optional($exam->ends_at)->format('Y-m-d\TH:i')) }}"
                class="w-full rounded border px-3 py-2"
            >
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $exam->is_published) ? 'checked' : '' }}>
        Publish ujian
    </label>

    <div class="flex items-center gap-2">
        <button class="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">Simpan Perubahan</button>
        <a href="{{ route('admin.exams.index') }}" class="rounded border border-slate-300 px-4 py-2 text-slate-700">Batal</a>
    </div>
</form>
@endsection
