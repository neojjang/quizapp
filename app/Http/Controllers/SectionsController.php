<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\ClassRoom;
use App\Models\QuizHeader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class SectionsController extends Controller
{
    public function createSection(ClassRoom $classRoom)
    {
        $classRoom = $classRoom;
        return view('admins.create_section', compact('classRoom'));
    }

    public function listSection()
    {

        $sections = Section::withCount('questions')->paginate(10);
        return view('admins.list_sections', compact('sections'));
    }

    public function storeSection(ClassRoom $classRoom, Request $request)
    {
        $classRoom = $classRoom;
        $data = $request->validate([
            'section.name' => 'required',
            'section.is_active' => 'required',
        ]);

        // `name`, `description`, `is_active`, `details`, `class_room_id`, `updated_at`, `created_at`
        $section = Section::create([
            'name' => $request->section['name'],
            'description' => (isset($request->section['description']) ? $request->section['description']:''),
            'is_active' => $request->section['is_active'],
            'details' => (isset($request->section['details']) ? $request->section['details']:''),
            'user_id' => Auth::id(),
            'class_room_id' => $classRoom->id
        ]);
        // auth()->user()->sections()->createMany($data);
        // $classRoom->sections()->add($section);
        return redirect()->route('listSection', $classRoom->id)->with('success', 'Section created successfully!');
    }

    public function editSection(Section $section)
    {
        return view('admins.edit_section', compact('section'));
    }

    public function updateSection(Section $section, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'nullable|min:5|max:255',
            'is_active' => 'required',
            'details' =>    'nullable|min:10|max:1024',
        ],[
            'name' => '섹션 이름은 필수입니다.',
            'is_active' => '섹션 활성화는 필수입니다.',
            'details.min' => '섹션 상세 설명은 10자 이상입니다.',
            'description.min' => '섹션 설명은 5자 이상입니다.',
        ]);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        $record = Section::findOrFail($section->id);
//        $input = $request->all();
        Log::debug($data);
        $record->fill($data)->save();
        session()->flash('success', 'Section saved successfully!');
        return redirect()->route('detailClassRoom', $section->class_room_id);
    }

    public function detailSection(Section $section)
    {
        $questions = $section->questions()->paginate(10);
        return view('admins.detail_sections', compact('questions', 'section'));
    }

    public function deleteSection($id)
    {
        //$sections = Section::paginate(10);
        $section = Section::findOrFail($id);
        $section->delete();
        return redirect()->back()->withSuccess('Section with id: ' . $section->id . ' deleted successfully');
    }

    public function scoreSection(Section $section)
    {
        // $questions = $section->questions()->paginate(10);
        // $quiz_headers = QuizHeader::where("section_id", $section->id)->orderBy("id", "DESC")->get();
        // $user_id_list = collect($quiz_users)->map(function($item) {
        //     return $item->user_id;
        // });
        // Log::debug($user_id_list);
        // $users = User::whereIn('id', $user_id_list)->orderBy('id', 'ASC')->get();
        $quiz_headers = $section->quizHeaders()->where("completed", "1")->paginate(30);
        return view('admins.score_sections', compact('section', 'quiz_headers')); // 'questions'
    }
}
