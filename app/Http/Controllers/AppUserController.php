<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\MajorGroup;
use App\Models\MediumGroup;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuizHeader;
use Illuminate\Support\Facades\Log;

class AppUserController extends Controller
{
    public function startQuiz(MajorGroup $majorGroup=null, MediumGroup $mediumGroup=null, ClassRoom $classRoom=null)
    {
        Log::info("AppUserController::startQuiz");
        Log::debug($majorGroup);
        Log::debug($mediumGroup);
        Log::debug($classRoom);
        if ($majorGroup && $mediumGroup && $classRoom) {
            return view('appusers.quiz', compact('majorGroup', 'mediumGroup', 'classRoom'));
        }

        $targetGroups = [];
        if (!isset($majorGroup) && !isset($mediumGroup) && !isset($classRoom)) {
            $targetGroups = MajorGroup::withCount('mediumGroups')->where('is_active', '1')->orderBy('id','desc')->paginate(20);
        } elseif (isset($majorGroup) && !isset($mediumGroup) && !isset($classRoom)) {
            $targetGroups = MediumGroup::withCount('classRooms')->where(
                'major_group_id', $majorGroup->id
            )->where('is_active', '1')->orderBy('id', 'desc')->paginate(20);
        } else {
            $targetGroups = ClassRoom::withCount('sections')->where(
                'medium_group_id', $mediumGroup->id
            )->where('is_active', '1')->orderBy('id', 'desc')->paginate(20);
        }
        return view('appusers.start_quiz', compact(
            'majorGroup', 'mediumGroup', 'classRoom', 'targetGroups'
        ));
    }

    public function testQuiz()
    {

    }

    public function userQuizHome()
    {
        $activeUsers = User::count();

        $questionsCount = Question::where('is_active', '1')->count();

        $sections = Section::withCount('questions')
            ->where('is_active', '1')
            ->orderBy('name')
            ->get();

        $quizesTaken = QuizHeader::count();

        $userQuizzes = auth()
            ->user()
            ->quizHeaders()
            ->orderBy('id', 'desc')
            ->paginate(10);

        $quizAverage = auth()->user()->quizHeaders()->avg('score');

        return view(
            'appusers.userQuizHome',
            compact(
                'sections',
                'activeUsers',
                'questionsCount',
                'quizesTaken',
                'userQuizzes',
                'quizAverage'
            )
        );
    }


    public function deleteUserQuiz($id)
    {
        $quizheader = QuizHeader::findOrFail($id);
        if (auth()->id() == $quizheader->user_id) {
            $quizheader->delete();
            return redirect()->back()
                ->withSuccess("Quiz deleted successfully!");
        }
        return redirect()->back()->withWarning("Can not delete quiz!");
    }
    public function userQuizDetails($id)
    {
        // Answers with alphabetical choice
        $choice = collect(['A', 'B', 'C', 'D', 'E']);
        $resultMark = ["0"=>"X", "1"=>"O", "2"=>"?"];

        //Get quiz summary record for the given quiz
        $userQuizDetails = QuizHeader::where('id', $id)->where('user_id', auth()->id())
            ->with('section')->first();

        //Extract question taken by the users stored as a serialized string while takeing the quiz
        $quizQuestionsList = collect(unserialize($userQuizDetails->questions_taken));

        //Get the actual quiz questiona and answers from Quiz table using quiz_header_id
        $userQuiz = Quiz::where('quiz_header_id', $userQuizDetails->id)
            ->orderBy('question_id', 'ASC')->get();
        //dd($userQuiz);
        //Get the Questions and related answers taken by the user during the quiz
        $quizQuestions = Question::whereIn('id', $quizQuestionsList)->orderBy('id', 'ASC')->with('answers')->get();

        //pass the data using compact to the view to display
        return view(
            'appusers.userQuizDetail',
            compact(
                'userQuizDetails',
                'quizQuestionsList',
                'userQuiz',
                'quizQuestions',
                'choice',
                'resultMark'
            )
        );
    }
}
