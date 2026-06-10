@extends('layouts.panel')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Buat Ujian Baru</h1>

<form method="POST" action="{{ route('admin.exams.store') }}" class="space-y-4 rounded bg-white p-6 shadow">
    @csrf

    <div>
        <label class="mb-1 block text-sm font-medium">Judul Ujian</label>
        <input name="title" value="{{ old('title') }}" class="w-full rounded border px-3 py-2" required>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium">Deskripsi</label>
        <textarea name="description" class="w-full rounded border px-3 py-2" rows="3">{{ old('description') }}</textarea>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-medium">Durasi (menit)</label>
            <input type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" class="w-full rounded border px-3 py-2" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Mulai</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="w-full rounded border px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Selesai</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="w-full rounded border px-3 py-2">
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_published" value="1" checked>
        Publish ujian
    </label>

    <button class="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">Simpan Ujian</button>
</form>
@endsection
