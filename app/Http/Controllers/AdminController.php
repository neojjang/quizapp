<?php

namespace App\Http\Controllers;

use App\Models\MajorGroup;
use App\Models\MediumGroup;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuizHeader;
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
        return view('admins.adminhome', compact(
            // 'latestUsers',
            'sectionCount', 'questionCount', 'userCount', 'classRoomCount',
            'majorGroupCount', 'mediumGroupCount'
        ));
    }
}
