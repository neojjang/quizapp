<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Section;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

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
        $section = $section;
        $data = $request->validate([
            'question' => ['required', Rule::unique('questions')],
            'explanation' => 'required',
            'is_active' => 'required',
            'answers.*.answer' => 'required',
            'answers.*.is_checked' => 'present'
        ]);


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
        return view('admins.create_question', compact('section', 'question', 'answers'));
    }

    public function updateQuestion(Question $question, Request $request)
    {
        $data = $request->validate([
            'question' => ['required', Rule::unique('questions')],
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
}
