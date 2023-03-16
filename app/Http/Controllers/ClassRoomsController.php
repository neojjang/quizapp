<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class ClassRoomsController extends Controller
{
    public function createClassRoom()
    {
        return view('admins.create_class_room');
    }

    public function listClassRoom()
    {
        $classRooms = ClassRoom::withCount('sections')->orderBy('updated_at','desc')->paginate(10);
        return view('admins.list_class_rooms', compact('classRooms'));
    }

    public function storeClassRoom(Request $request)
    {
        $data = $request->validate([
            'class_room.name' => 'required',
            'class_room.is_active' => 'required'
        ],[
            'class_room.name' => '수업 이름은 필수입니다.',
            'class_room.is_active' => '수업 활성화는 필수입니다.',
        ]);

        if (!isset($data['class_room']['description'])) {
            $data['class_room']['description'] = '';
        }
        if (!isset($data['class_room']['details'])) {
            $data['class_room']['details'] = '';
        }
        auth()->user()->classRooms()->createMany($data);
        return redirect()->route('listClassRoom')->with('success', 'ClassRoom created successfully!');
    }

    public function editClassRoom(ClassRoom $classRoom)
    {
        return view('admins.edit_class_room', compact('classRoom'));
    }

    public function updateClassRoom(ClassRoom $classRoom, Request $request)
    {
//        Log::debug($request->all());
        $data = $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'nullable|min:5|max:255',
            'is_active' => 'required',
            'details' =>    'nullable|min:10|max:1024',
        ],[
            'name' => '수업 이름은 필수입니다.',
            'is_active' => '수업 활성화는 필수입니다.',
            'details.min' => '수업 상세 설명은 10자 이상입니다.',
            'description.min' => '수업 설명은 5자 이상입니다.',
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
        $sections = $classRoom->sections()->paginate(10);
        return view('admins.detail_class_rooms', compact('sections', 'classRoom'));
    }

    public function deleteClassRoom($id)
    {
        $classRoom = ClassRoom::findOrFail($id);
        $classRoom->delete();
        return redirect()->back()->withSuccess('ClassRoom with id: ' . $id . ' deleted successfully');
    }
}
