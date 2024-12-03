<?php

namespace App\Http\Controllers;

use App\Models\MajorGroup;
use App\Models\MediumGroup;
use Illuminate\Http\Request;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class ClassRoomsController extends Controller
{
    public function create()
    {
        return $this->_create(null);
    }
    public function createClassRoom(MediumGroup $mediumGroup)
    {
        return $this->_create($mediumGroup);
    }

    public function listClassRoom()
    {
        $classRooms = ClassRoom::withCount('sections')->orderBy('updated_at','desc')->paginate(10);
        return view('admins.list_class_rooms', compact('classRooms'));
    }

    public function storeWithMediumGroup(MediumGroup $mediumGroup, Request $request)
    {
        return $this->_store($mediumGroup, $request);
    }

    public function store(Request $request)
    {
        return $this->_store(null, $request);
    }

    public function editClassRoom(ClassRoom $classRoom)
    {
        $mediumGroups = MediumGroup::query()->where('is_active', '1')->orderBy('id', 'desc')->get();
        return view('admins.edit_class_room', compact('classRoom', 'mediumGroups'));
    }

    public function updateClassRoom(ClassRoom $classRoom, Request $request)
    {
//        Log::debug($request->all());
        $data = $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'nullable|min:5|max:255',
            'is_active' => 'required',
            'details' =>    'nullable|min:10|max:1024',
            'medium_group_id' => 'required|int',
        ],[
            'name' => '수업 이름은 필수입니다.',
            'is_active' => '수업 활성화는 필수입니다.',
            'details.min' => '수업 상세 설명은 10자 이상입니다.',
            'description.min' => '수업 설명은 5자 이상입니다.',
            'medium_group_id' => '수업 중분류 선택은 필수입니다.'
        ]);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        $record = ClassRoom::findOrFail($classRoom->id);
//        $input = $request->all();
//        Log::debug($data);
        $record->fill($data)->save();
        session()->flash('success', 'ClassRoom saved successfully!');
        return redirect()->route('detailClassRoom', $classRoom->id);
    }

    public function detailClassRoom(ClassRoom $classRoom)
    {
        $sections = $classRoom->sections()->orderBy('updated_at', 'desc')->paginate(10);
        return view('admins.detail_class_rooms', compact('sections', 'classRoom'));
    }

    public function deleteClassRoom($id)
    {
        $classRoom = ClassRoom::findOrFail($id);
        $classRoom->delete();
        return redirect()->back()->withSuccess('ClassRoom with id: ' . $id . ' deleted successfully');
    }

    private function _create($mediumGroup)
    {
        $mediumGroups = null;
        if (!isset($mediumGroup)) {
            $mediumGroups = MediumGroup::query()->where('is_active', '1')->orderBy('id', 'desc')->get();
        }
        return view('admins.create_class_room', compact('mediumGroup', 'mediumGroups'));
    }

    private function _store($mediumGroup, Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'is_active' => 'required',
            'description' => 'nullable|max:255',
            'details' =>    'nullable|max:2048',
            'medium_group_id' => 'nullable|int',
        ],[
            'name' => '수업 이름은 필수입니다.',
            'is_active' => '수업 활성화는 필수입니다.',
        ]);

        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        if (isset($mediumGroup)) $data['medium_group_id'] = $mediumGroup->id;
        Log::debug($data);
        auth()->user()->classRooms()->createMany([$data]);
        return redirect()->route('listClassRoom')->with('success', 'ClassRoom created successfully!');
    }
}
