<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamTimeExtension;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ExtensionController extends Controller
{
    public function index(): View
    {
        $exams = Exam::query()
            ->where('is_published', true)
            ->orderBy('title')
            ->get();

        $registrations = ExamRegistration::query()
            ->with('user:id,name,email,role')
            ->whereIn('exam_id', $exams->pluck('id'))
            ->get();

        $registeredUsersByExam = $registrations
            ->groupBy('exam_id')
            ->map(fn ($items) => $items
                ->pluck('user')
                ->filter(fn ($user) => $user && $user->role === 'user')
                ->unique('id')
                ->values()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
            );

        $extensions = ExamTimeExtension::query()
            ->with(['exam', 'user', 'admin'])
            ->latest()
            ->paginate(20);

        return view('admin.extensions.index', compact('exams', 'registeredUsersByExam', 'extensions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exam_id' => [
                'required',
                Rule::exists('exams', 'id')->where(fn ($query) => $query->where('is_published', true)),
            ],
            'user_id' => [
                'required',
                Rule::exists('exam_registrations', 'user_id')->where(
                    fn ($query) => $query->where('exam_id', $request->input('exam_id'))
                ),
            ],
            'extra_seconds' => ['required', 'integer', 'min:30'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (! User::query()->where('id', $validated['user_id'])->where('role', 'user')->exists()) {
            return back()
                ->withErrors(['user_id' => 'User tidak valid untuk tambahan waktu ujian.'])
                ->withInput();
        }

        ExamTimeExtension::create([
            ...$validated,
            'granted_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Tambahan waktu berhasil diberikan.');
    }
}
