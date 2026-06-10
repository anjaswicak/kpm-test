<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::query()->withCount('questions')->latest()->paginate(10);

        return view('admin.exams.index', compact('exams'));
    }

    public function create(): View
    {
        return view('admin.exams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        // check is published
        // dd($validated['is_published']);
        // dd($validated);

        $exam = Exam::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        return redirect()->route('admin.exams.show', $exam)->with('status', 'Ujian berhasil dibuat.');
    }

    public function show(Exam $exam): View
    {
        $exam->load(['questions' => fn ($q) => $q->orderBy('question_number')]);

        return view('admin.exams.show', compact('exam'));
    }

    public function edit(Exam $exam): View
    {
        return view('admin.exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $exam->update([
            ...$validated,
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        return redirect()->route('admin.exams.index')->with('status', 'Ujian berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:publish,unpublish,close'],
        ]);

        if ($validated['action'] === 'publish') {
            $exam->update([
                'is_published' => true,
                'ends_at' => $exam->ends_at,
            ]);
        }

        if ($validated['action'] === 'unpublish') {
            $exam->update([
                'is_published' => false,
            ]);
        }

        if ($validated['action'] === 'close') {
            $exam->update([
                'is_published' => false,
                'ends_at' => now(),
            ]);
        }

        return redirect()->route('admin.exams.index')->with('status', 'Status ujian berhasil diperbarui.');
    }
}
