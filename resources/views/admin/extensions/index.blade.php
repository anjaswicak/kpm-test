@extends('layouts.panel')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Tambahan Waktu Ujian</h1>

<form method="POST" action="{{ route('admin.extensions.store') }}" class="mb-6 grid gap-3 rounded bg-white p-5 shadow md:grid-cols-4">
    @csrf
    <select id="exam_id" name="exam_id" class="rounded border px-3 py-2" required>
        <option value="">Pilih ujian</option>
        @foreach ($exams as $exam)
            <option value="{{ $exam->id }}" @selected(old('exam_id') == $exam->id)>{{ $exam->title }}</option>
        @endforeach
    </select>

    <select id="user_id" name="user_id" class="rounded border px-3 py-2" required>
        <option value="">Pilih user</option>
    </select>

    <input type="number" min="30" name="extra_seconds" class="rounded border px-3 py-2" placeholder="Detik tambahan" required>
    <input name="reason" class="rounded border px-3 py-2" placeholder="Alasan (opsional)">

    <button class="md:col-span-4 rounded bg-indigo-600 px-4 py-2 text-white" type="submit">Berikan Tambahan Waktu</button>
</form>

<div class="overflow-x-auto rounded bg-white shadow">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr>
                <th class="px-4 py-3">Waktu</th>
                <th class="px-4 py-3">Ujian</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Tambahan</th>
                <th class="px-4 py-3">Admin</th>
                <th class="px-4 py-3">Alasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($extensions as $ext)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $ext->created_at->format('d-m-Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $ext->exam->title }}</td>
                    <td class="px-4 py-3">{{ $ext->user->name }}</td>
                    <td class="px-4 py-3">{{ $ext->extra_seconds }} detik</td>
                    <td class="px-4 py-3">{{ $ext->admin->name }}</td>
                    <td class="px-4 py-3">{{ $ext->reason }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada tambahan waktu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $extensions->links() }}</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const usersByExam = @json($registeredUsersByExam);
        const examSelect = document.getElementById('exam_id');
        const userSelect = document.getElementById('user_id');
        const oldUserId = @json(old('user_id'));

        const resetUserOptions = () => {
            userSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Pilih user';
            userSelect.appendChild(placeholder);
        };

        const fillUsers = (examId) => {
            resetUserOptions();

            const users = usersByExam[String(examId)] || [];
            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                if (String(oldUserId) === String(user.id)) {
                    option.selected = true;
                }
                userSelect.appendChild(option);
            });

            userSelect.disabled = users.length === 0;
        };

        examSelect.addEventListener('change', () => {
            fillUsers(examSelect.value);
        });

        fillUsers(examSelect.value);
    });
</script>
@endsection
