<?php

namespace App\Http\Controllers;

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
        $section = $section;
        return view('admins.create_question', compact('section'));
    }

    public function detailQuestion(Question $question)
    {
        $answers = $question->answers()->paginate(10);
        return view('admins.detail_question', compact('question', 'answers'));
    }

    public function storeQuestion(Section $section, Request $request)
    {
	Log::debug("storeQuestion...");
	Log::debug($request);
        $section = $section;

        $data = $request->validate([
            'question' => ['required', Rule::unique('questions')],
            'explanation' => 'required',
            'is_active' => 'required',
	    'type_id' => ['required','numeric', 'in:1,2'],
            //'answers.*.answer' => 'required',
            'answers.0.answer' => 'required',
            'answers.1.answer' => 'nullable',
            'answers.2.answer' => 'nullable',
            'answers.3.answer' => 'nullable',
            'answers.*.is_checked' => 'present'
        ],[
            'question.required' => '문제는 필수 항목입니다.',
            'explanation.required' => '문제설명은 필수 항목입니다.',
            'is_active.required' => 'is_active 필수 항목입니다.',
            'type_id.numeric' => 'type_id는 숫자입니다.',
            'answers.0.answer.required' => 'answer는 필수입니다.',
            'answers.1.answer.required' => 'answer는 필수입니다.',
            'answers.2.answer.required' => 'answer는 필수입니다.',
            'answers.3.answer.required' => 'answer는 필수입니다.',
        ]);

	Log::debug($data);
        $question = Question::create([
            'question' => $request->question,
            'explanation' => $request->explanation,
            'is_active' => $request->is_active,
            'user_id' => Auth::id(),
            'section_id' => $section->id,
	    'type_id' => isset($request->type_id) ? $request->type_id:1
        ]);

        $status = $question->answers()->createMany($data['answers'])->push();
        return redirect()->route('detailSection', $section->id)
            ->withSuccess('Question created successfully');
    }

    public function editQuestion(Question $question)
    {
        $section = $question->section();
        $answers = $question->answers()->paginate(10);
        return view('admins.edit_question', compact('section', 'question', 'answers'));
    }

    public function updateQuestion(Question $question, Request $request)
    {
        $data = $request->validate([
            'question' => ['required'],
            'explanation' => 'required',
            'is_active' => 'required',
            'answers.*.answer' => 'required',
            'answers.*.is_checked' => 'present'
        ]);

        $question->question = $data['question'];
        $question->explanation = $data['explanation'];
        $question->is_active = $data['is_active'];
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
        $choice = collect(['A', 'B', 'C', 'D']);

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
        
        return view('admins.score_questions', compact('section', 'user', 'questions', 'userQuiz', 'quizHeader', 'choice')); // , ["userQuiz" => $userQuiz]
    }
}
