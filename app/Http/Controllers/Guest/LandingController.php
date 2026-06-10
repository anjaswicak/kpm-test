<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $upcomingExams = Exam::query()
            ->where('is_published', true)
            ->orderBy('starts_at')
            ->limit(6)
            ->get();

        return view('pages.homepage', compact('upcomingExams'));
    }
}
