<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRegistration;
use App\Models\ExamTimeExtension;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExamController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $exams = Exam::query()
            ->where('is_published', true)
            ->withCount('questions')
            ->orderBy('starts_at')
            ->get();

        $registeredExamIds = ExamRegistration::query()
            ->where('user_id', $user->id)
            ->pluck('exam_id')
            ->all();

        $attemptsByExamId = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('exam_id');

        $extensionSecondsByExamId = ExamTimeExtension::query()
            ->where('user_id', $user->id)
            ->selectRaw('exam_id, SUM(extra_seconds) as total_extra_seconds')
            ->groupBy('exam_id')
            ->pluck('total_extra_seconds', 'exam_id');

        return view('user.exams.index', compact(
            'exams',
            'registeredExamIds',
            'attemptsByExamId',
            'extensionSecondsByExamId'
        ));
    }

    public function register(Request $request, Exam $exam): RedirectResponse
    {
        ExamRegistration::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => 'registered',
                'registered_at' => now(),
            ]
        );

        return back()->with('status', 'Berhasil mendaftar ujian.');
    }
}
