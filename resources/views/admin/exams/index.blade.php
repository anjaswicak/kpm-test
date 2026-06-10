@extends('layouts.panel')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Daftar Ujian</h1>
    <a href="{{ route('admin.exams.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-white">Buat Ujian</a>
</div>

<div class="overflow-x-auto rounded bg-white shadow">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Durasi</th>
                <th class="px-4 py-3">Jumlah Soal</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($exams as $exam)
                @php
                    $isClosed = $exam->ends_at && $exam->ends_at->isPast();
                    $isInExamRange = $exam->starts_at
                        && $exam->ends_at
                        && now()->between($exam->starts_at, $exam->ends_at);
                @endphp
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $exam->title }}</td>
                    <td class="px-4 py-3">{{ $exam->duration_minutes }} menit</td>
                    <td class="px-4 py-3">{{ $exam->questions_count }}</td>
                    <td class="px-4 py-3">
                        @if ($isClosed)
                            <span class="rounded bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700">Closed</span>
                        @elseif ($exam->is_published)
                            <span class="rounded bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Published</span>
                        @else
                            <span class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Unpublished</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-2">
                            @unless ($isInExamRange)
                                <a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.show', $exam) }}">Kelola Soal</a>
                            @endunless
                            <a class="rounded bg-sky-600 px-2 py-1 text-xs text-white" href="{{ route('admin.exams.edit', $exam) }}">Edit</a>

                            @if (! $isClosed)
                                @if (! $exam->is_published)
                                    <form method="POST" action="{{ route('admin.exams.status', $exam) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="publish">
                                        <button type="submit" class="rounded bg-emerald-600 px-2 py-1 text-xs text-white">
                                            Publish
                                        </button>
                                    </form>
                                @endif

                                @if ($exam->is_published)
                                    <form method="POST" action="{{ route('admin.exams.status', $exam) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="unpublish">
                                        <button type="submit" class="rounded bg-amber-600 px-2 py-1 text-xs text-white">
                                            Unpublish
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.exams.status', $exam) }}" onsubmit="return confirm('Tutup ujian ini sekarang?');">
                                    @csrf
                                    <input type="hidden" name="action" value="close">
                                    <button type="submit" class="rounded bg-rose-700 px-2 py-1 text-xs text-white">
                                        Close
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada ujian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $exams->links() }}</div>
@endsection
