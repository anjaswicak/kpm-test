<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'option_a' => ['nullable', 'string'],
            'option_b' => ['nullable', 'string'],
            'option_c' => ['nullable', 'string'],
            'option_d' => ['nullable', 'string'],
            'correct_answer' => ['nullable', 'in:A,B,C,D'],
            'points' => ['required', 'integer', 'min:1'],
        ]);

        $nextNumber = (int) $exam->questions()->max('question_number') + 1;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('questions', 'public');
        }

        $options = array_filter([
            'A' => $validated['option_a'] ?? null,
            'B' => $validated['option_b'] ?? null,
            'C' => $validated['option_c'] ?? null,
            'D' => $validated['option_d'] ?? null,
        ]);

        ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_number' => $nextNumber,
            'question_text' => $validated['question_text'],
            'image_path' => $imagePath,
            'options' => $options,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'points' => $validated['points'],
        ]);

        $exam->update([
            'question_count' => $exam->questions()->count(),
        ]);

        return back()->with('status', 'Soal berhasil ditambahkan.');
    }
}
