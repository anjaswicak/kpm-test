<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamTimeExtension;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class AttemptController extends Controller
{
    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();

        $registration = ExamRegistration::query()->firstWhere([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
        ]);

        if (! $registration) {
            return redirect()->route('user.exams.index')->with('status', 'Silakan daftar ujian terlebih dahulu.');
        }

        $attempt = ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $user->id,
            ],
            [
                'started_at' => now(),
            ]
        );

        if (! $attempt->started_at) {
            $attempt->update(['started_at' => now()]);
        }

        $extraSeconds = (int) ExamTimeExtension::query()
            ->where('exam_id', $attempt->exam_id)
            ->where('user_id', $attempt->user_id)
            ->sum('extra_seconds');

        $allowedSeconds = ($attempt->exam->duration_minutes * 60) + $extraSeconds;
        $endsAt = $attempt->started_at->copy()->addSeconds($allowedSeconds);

        $resetViolations = false;

        if ($attempt->submitted_at && now()->lessThan($endsAt)) {
            $attempt->update([
                'submitted_at' => null,
                'duration_seconds' => 0,
                'total_score' => 0,
            ]);
            $resetViolations = true;
        }

        $registration->update(['status' => 'started']);

        return redirect()
            ->route('user.attempts.show', $attempt)
            ->with('reset_violations', $resetViolations);
    }

    public function show(Request $request, ExamAttempt $attempt): View|RedirectResponse
    {
        $this->ensureOwner($request, $attempt);

        $resetViolations = (bool) $request->session()->pull('reset_violations', false);

        $attempt->load([
            'exam.questions' => fn ($q) => $q->orderBy('question_number'),
            'answers',
        ]);

        $extraSeconds = (int) ExamTimeExtension::query()
            ->where('exam_id', $attempt->exam_id)
            ->where('user_id', $attempt->user_id)
            ->sum('extra_seconds');

        $allowedSeconds = ($attempt->exam->duration_minutes * 60) + $extraSeconds;
        $endsAt = $attempt->started_at->copy()->addSeconds($allowedSeconds);
        $remainingSeconds = max(0, $endsAt->timestamp - now()->timestamp);

        if ($attempt->submitted_at) {
            if (now()->greaterThanOrEqualTo($endsAt)) {
                return redirect()->route('user.attempts.review', $attempt);
            }

            $attempt->update([
                'submitted_at' => null,
                'duration_seconds' => 0,
                'total_score' => 0,
            ]);

            ExamRegistration::query()
                ->where('exam_id', $attempt->exam_id)
                ->where('user_id', $attempt->user_id)
                ->update(['status' => 'started']);

            $resetViolations = true;

            $attempt->refresh();
            $attempt->load([
                'exam.questions' => fn ($q) => $q->orderBy('question_number'),
                'answers',
            ]);
        }

        if (now()->greaterThan($endsAt)) {
            $this->submitAttempt($attempt, $allowedSeconds);
            return redirect()->route('user.attempts.review', $attempt);
        }

        $questionIndex = max(1, min((int) $request->query('q', 1), $attempt->exam->questions->count()));
        $currentQuestion = $attempt->exam->questions[$questionIndex - 1] ?? null;
        $answersByQuestion = $attempt->answers->keyBy('exam_question_id');

        return view('user.attempts.show', compact(
            'attempt',
            'currentQuestion',
            'questionIndex',
            'answersByQuestion',
            'endsAt',
            'allowedSeconds',
            'remainingSeconds',
            'resetViolations'
        ));
    }

    public function autosave(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->ensureOwner($request, $attempt);

        if ($attempt->submitted_at) {
            return response()->json(['message' => 'Attempt already submitted.'], 422);
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:exam_questions,id'],
            'answers.*.answer_option' => ['nullable', 'string', 'max:255'],
            'answers.*.answer_text' => ['nullable', 'string'],
        ]);

        foreach ($validated['answers'] as $answerData) {
            ExamAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $answerData['question_id'],
                ],
                [
                    'answer_option' => $answerData['answer_option'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'last_saved_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'saved']);
    }

    public function timeStatus(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->ensureOwner($request, $attempt);

        $attempt->loadMissing('exam');

        if (! $attempt->started_at) {
            return response()->json([
                'remaining_seconds' => 0,
                'allowed_seconds' => 0,
                'ends_at' => null,
            ]);
        }

        $extraSeconds = (int) ExamTimeExtension::query()
            ->where('exam_id', $attempt->exam_id)
            ->where('user_id', $attempt->user_id)
            ->sum('extra_seconds');

        $allowedSeconds = ($attempt->exam->duration_minutes * 60) + $extraSeconds;
        $endsAt = $attempt->started_at->copy()->addSeconds($allowedSeconds);
        $remainingSeconds = max(0, $endsAt->timestamp - now()->timestamp);

        return response()->json([
            'remaining_seconds' => $remainingSeconds,
            'allowed_seconds' => $allowedSeconds,
            'ends_at' => $endsAt->toIso8601String(),
        ]);
    }

    public function submit(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $this->ensureOwner($request, $attempt);

        $extraSeconds = (int) ExamTimeExtension::query()
            ->where('exam_id', $attempt->exam_id)
            ->where('user_id', $attempt->user_id)
            ->sum('extra_seconds');

        $allowedSeconds = ($attempt->exam->duration_minutes * 60) + $extraSeconds;
        $this->submitAttempt($attempt, $allowedSeconds);

        return redirect()->route('user.attempts.review', $attempt)->with('status', 'Ujian berhasil dikumpulkan.');
    }

    public function review(Request $request, ExamAttempt $attempt): View
    {
        $this->ensureOwner($request, $attempt);

        $attempt->load([
            'exam.questions' => fn ($q) => $q->orderBy('question_number'),
            'answers',
        ]);

        $answersByQuestion = $attempt->answers->keyBy('exam_question_id');

        // dd($answersByQuestion);exit;
        return view('user.attempts.review', compact('attempt', 'answersByQuestion'));
    }

    private function ensureOwner(Request $request, ExamAttempt $attempt): void
    {
        if ($attempt->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function submitAttempt(ExamAttempt $attempt, int $allowedSeconds): void
    {
        if ($attempt->submitted_at) {
            return;
        }

        $allowedSeconds = max(0, (int) $allowedSeconds);

        $attempt->loadMissing(['exam.questions', 'answers']);
        $answers = $attempt->answers->keyBy('exam_question_id');

        $totalScore = 0;
        foreach ($attempt->exam->questions as $question) {
            $answer = $answers->get($question->id);

            if (! $answer) {
                continue;
            }

            $actual = $answer->answer_option ?: $answer->answer_text;
            $isCorrect = $question->correct_answer
                ? mb_strtolower(trim((string) $actual)) === mb_strtolower(trim((string) $question->correct_answer))
                : null;

            $answer->update([
                'is_correct' => $isCorrect,
            ]);

            if ($isCorrect) {
                $totalScore += $question->points;
            }
        }

        $startedAt = $attempt->started_at ?? now();
        $elapsedSeconds = (int) floor(now()->floatDiffInSeconds($startedAt, false));
        $elapsedSeconds = max(0, $elapsedSeconds);
        $duration = min($elapsedSeconds, $allowedSeconds);

        $attempt->update([
            'submitted_at' => now(),
            'duration_seconds' => $duration,
            'total_score' => $totalScore,
        ]);

        ExamRegistration::query()
            ->where('exam_id', $attempt->exam_id)
            ->where('user_id', $attempt->user_id)
            ->update(['status' => 'submitted']);
    }
}
