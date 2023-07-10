<?php

namespace App\Http\Controllers;

use App\Constants\Question as ConstQuestion;
use App\Models\User;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuizHeader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class QuestionsController extends Controller
{
    public function createQuestion(Section $section)
    {
        $question_types = ConstQuestion::TYPES;
        $section = $section;
        return view('admins.create_question', compact('section', 'question_types'));
    }

    public function detailQuestion(Question $question)
    {
        $question_types = ConstQuestion::TYPES;
        $answers = $question->answers()->paginate(10);
        return view('admins.detail_question', compact('question', 'answers', 'question_types'));
    }

    public function storeQuestion(Section $section, Request $request)
    {
	Log::debug("storeQuestion...");
	Log::debug($request);
        $section = $section;

        $data = $request->validate([
            'question' => ['required', Rule::unique('questions')->where(function ($query) use ($section, $request) {
                return $query
                    ->where('question', $request->question)
                    ->where('section_id', $section->id);
            })],
            'is_active' => 'required',
	        'type_id' => ['required','numeric', 'in:1,2,3'],
            //'answers.*.answer' => 'required',
            'answers.0.answer' => 'required',
            'answers.1.answer' => 'nullable',
            'answers.2.answer' => 'nullable',
            'answers.3.answer' => 'nullable',
            'answers.4.answer' => 'nullable',
            'answers.*.is_checked' => 'present'
        ],[
            'question.unique' => '다른 문제와 중복 됩니다.',
            'question.required' => '문제는 필수 항목입니다.',
            'is_active.required' => 'is_active 필수 항목입니다.',
            'type_id.numeric' => 'type_id는 숫자입니다.',
            'answers.0.answer.required' => 'answer는 필수입니다.',
            'answers.1.answer.required' => 'answer는 필수입니다.',
            'answers.2.answer.required' => 'answer는 필수입니다.',
            'answers.3.answer.required' => 'answer는 필수입니다.',
            'answers.4.answer.required' => 'answer는 필수입니다.',
        ]);

        $answers = array_filter($data['answers'], function($v, $k) {
            return isset($v['answer']);
        }, ARRAY_FILTER_USE_BOTH);
        Log::debug($answers);

        $question = Question::create([
            'question' => $request->question,
            'explanation' => (isset($data['explanation']) ? $request->explanation : ''),
            'is_active' => $request->is_active,
            'user_id' => Auth::id(),
            'section_id' => $section->id,
	        'type_id' => isset($request->type_id) ? $request->type_id:1
        ]);

        $status = $question->answers()->createMany($answers)->push();
        return redirect()->route('detailSection', $section->id)
            ->withSuccess('Question created successfully');
    }

    public function editQuestion(Question $question)
    {
        $question_types = ConstQuestion::TYPES;
        $section = $question->section();
        $answers = $question->answers()->paginate(10);
        return view('admins.edit_question', compact('section', 'question', 'answers', 'question_types'));
    }

    public function updateQuestion(Question $question, Request $request)
    {
        $data = $request->validate([
            'question' => ['required'],
            'explanation' => 'nullable',
            'is_active' => 'required',
            'type_id' => 'required',
            'answers.0.answer' => 'required',
            'answers.1.answer' => 'nullable',
            'answers.2.answer' => 'nullable',
            'answers.3.answer' => 'nullable',
            'answers.4.answer' => 'nullable',
            'answers.*.is_checked' => 'present'
        ]);

        $question->question = $data['question'];
        $question->explanation = (isset($data['explanation']) ? $request->explanation : '');
        $question->is_active = $data['is_active'];
        $question->type_id = $data['type_id'];
        $question->save();

        $question->answers()->delete();
        $status = $question->answers()->createMany($data['answers'])->push();
        return redirect()->route('detailSection', $question->section_id)
            ->withSuccess('Question created successfully');
    }

    function deleteQuestion($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        return redirect()->route('detailSection', $question->section->id)
            ->withSuccess('Question with id: ' . $question->id . ' deleted successfully');
    }

    function scoreQuestion(Section $section, QuizHeader $quizHeader)
    {
        $choice = collect(['A', 'B', 'C', 'D', 'E']);
        $resultMark = ["0"=>"X", "1"=>"O", "2"=>"?"];

        $questions = $section->questions;
        $quizzes = $quizHeader->quizzes;
        $user = $quizHeader->user;

        $userQuiz = [];
        foreach($quizzes as $quiz) {
            $userQuiz[$quiz->question_id] = $quiz;
        }
        // $userQuiz = collect($quizzes)->map(function($item) {
        //     return [$item->question_id => $item];
        // });
        Log::debug(($userQuiz));
        // $questions = collect($questions)->transform(function($item) {
        //     return $item;
        // });

        return view('admins.score_questions', compact('section', 'user', 'questions', 'userQuiz', 'quizHeader', 'choice', 'resultMark')); // , ["userQuiz" => $userQuiz]
    }

    public function createOMRSheet(Section $section)
    {
        $question_types = ConstQuestion::TYPES;
        $section = $section;
        return view('admins.create_omr_sheet', compact('section', 'question_types'));
    }
}
