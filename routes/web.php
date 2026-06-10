<?php

use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\ExtensionController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Guest\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AttemptController;
use App\Http\Controllers\User\ExamController as UserExamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // if (auth()->check()) {
    //     return redirect()->route('dashboard');
    // }

    return app(LandingController::class)->index();
})->name('landing');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/exams', [AdminExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [AdminExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [AdminExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}/edit', [AdminExamController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{exam}', [AdminExamController::class, 'update'])->name('exams.update');
    Route::get('/exams/{exam}', [AdminExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/status', [AdminExamController::class, 'updateStatus'])->name('exams.status');

    Route::post('/exams/{exam}/questions', [QuestionController::class, 'store'])->name('questions.store');

    Route::get('/extensions', [ExtensionController::class, 'index'])->name('extensions.index');
    Route::post('/extensions', [ExtensionController::class, 'store'])->name('extensions.store');
});

Route::prefix('user')->name('user.')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/exams', [UserExamController::class, 'index'])->name('exams.index');
    Route::post('/exams/{exam}/register', [UserExamController::class, 'register'])->name('exams.register');

    Route::post('/exams/{exam}/start', [AttemptController::class, 'start'])->name('attempts.start');
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
    Route::get('/attempts/{attempt}/time-status', [AttemptController::class, 'timeStatus'])->name('attempts.time-status');
    Route::post('/attempts/{attempt}/autosave', [AttemptController::class, 'autosave'])->name('attempts.autosave');
    Route::post('/attempts/{attempt}/submit', [AttemptController::class, 'submit'])->name('attempts.submit');
    Route::get('/attempts/{attempt}/review', [AttemptController::class, 'review'])->name('attempts.review');
});

require __DIR__.'/auth.php';
