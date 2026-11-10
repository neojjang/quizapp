<?php

namespace App\Http\Controllers;

use App\Models\MajorGroup;
use App\Models\MediumGroup;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuizHeader;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function adminhome()
    {
        $majorGroupCount = MajorGroup::count();
        $mediumGroupCount = MediumGroup::count();
        $classRoomCount = ClassRoom::count();
        $sectionCount = Section::count();
        $questionCount = Question::count();
        $userCount = User::count();
        // $latestUsers = User::latest()->take(5)->get();

        // 최근 100건 응시 점수(시간순)
        $recentQuizzes = QuizHeader::where('completed', true)->latest('id')->take(100)->get(['score', 'created_at'])->reverse();
        $scoreChart = [
            'labels' => $recentQuizzes->map(fn ($quiz) => $quiz->created_at->format('m-d'))->values(),
            'data' => $recentQuizzes->pluck('score')->values(),
        ];

        // 최근 30일 일별 가입자 수(가입자가 없는 날은 0). 날짜 그룹핑은 DB 종류와 무관하게 PHP에서 처리
        $from = now()->subDays(29)->startOfDay();
        $signups = User::where('created_at', '>=', $from)->pluck('created_at')
            ->countBy(fn ($createdAt) => $createdAt->format('m-d'));
        $days = collect(CarbonPeriod::create($from, now()))->map(fn ($day) => $day->format('m-d'));
        $userChart = [
            'labels' => $days,
            'data' => $days->map(fn ($day) => $signups->get($day, 0)),
        ];

        return view('admins.adminhome', compact(
            // 'latestUsers',
            'sectionCount', 'questionCount', 'userCount', 'classRoomCount',
            'majorGroupCount', 'mediumGroupCount', 'scoreChart', 'userChart'
        ));
    }
}
