<?php

namespace App\Http\Controllers;

use App\Constants\Section as ConstSection;
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
        $section_types = ConstSection::TYPES;
        $classRoom = $classRoom;
        return view('admins.create_section', compact('classRoom', 'section_types'));
    }

    public function listSection()
    {

        $sections = Section::withCount('questions')->orderBy('updated_at','desc')->paginate(20);
        return view('admins.list_sections', compact('sections'));
    }

    public function storeSection(ClassRoom $classRoom, Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'is_active' => 'required',
            'description' => 'nullable|max:255',
            'details' =>    'nullable|max:2048',
        ], [
            "name" => '이름은 필수입니다.',
            "is_active"=> '공개여부는 필수입니다.'
        ]);

//        $section = Section::create([
//            'name' => $data['name'],
//            'description' => (isset($data['description']) ? $data['description']:''),
//            'is_active' => $data['is_active'],
//            'type_id' => $data['type_id'],
//            'details' => (isset($data['details']) ? $data['details']:''),
//            'user_id' => Auth::id(),
//            'class_room_id' => $classRoom->id
//        ]);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        if (isset($classRoom)) {
            $data['class_room_id'] = $classRoom->id;
        }
        auth()->user()->sections()->createMany([$data]);
        return redirect()->route('detailClassRoom', $classRoom->id)->with('success', 'Section created successfully!');
//        return redirect()->route('listSection', $classRoom->id)->with('success', 'Section created successfully!');
    }

    public function editSection(Section $section)
    {
        $section_types = ConstSection::TYPES;
        $classRooms = ClassRoom::query()->where('is_active', '1')->orderBy('id', 'desc')->get();
        return view('admins.edit_section', compact('section', 'section_types', 'classRooms'));
    }

    public function updateSection(Section $section, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'nullable|min:5|max:255',
            'is_active' => 'required',
            'type_id' => 'required',
            'details' =>    'nullable|min:10|max:1024',
            'class_room_id' => 'required|int'
        ],[
            'name.required' => '섹션 이름은 필수입니다.',
            'is_active.required' => '섹션 활성화는 필수입니다.',
            'type_id.required' => '섹션 활성화는 필수입니다.',
            'details.min' => '섹션 상세 설명은 10자 이상입니다.',
            'description.min' => '섹션 설명은 5자 이상입니다.',
            'class_room_id' => '수업 선택은 필수입니다.'
        ]);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        if (!isset($data['type_id'])) {
            $data['type_id'] = (ConstSection::NORMAL);
        }
        $record = Section::findOrFail($section->id);
//        $input = $request->all();
//        Log::debug($data);
        $record->fill($data)->save();
        session()->flash('success', 'Section saved successfully!');
        return redirect()->route('detailSection', $section->id);
    }

    public function detailSection(Section $section)
    {
        $questions = $section->questions()->paginate(20);
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
