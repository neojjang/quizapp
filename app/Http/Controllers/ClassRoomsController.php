<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class ClassRoomsController extends Controller
{
    public function createClassRoom()
    {
        return view('admins.create_class_room');
    }

    public function listClassRoom()
    {
        $classRooms = ClassRoom::withCount('sections')->paginate(10);
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
        $data = $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'required|min:5|max:255',
            'is_active' => 'required',
            'details' =>    'required|min:10|max:1024',
        ]);
        $record = ClassRoom::findOrFail($section->id);
        $input = $request->all();
        $record->fill($input)->save();
        session()->flash('success', 'Section saved successfully!');
        return redirect()->route('listClassRoom');
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
        return redirect()->back()->withSuccess('ClassRoom with id: ' . $section->id . ' deleted successfully');
    }
}
