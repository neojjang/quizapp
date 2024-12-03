<?php

namespace App\Http\Controllers;

use App\Models\MajorGroup;
use App\Models\MediumGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediumGroupController extends Controller
{
    public function create()
    {
        return $this->_createMediumGroup(null);
    }
    public function createWithMajorGroup(MajorGroup $majorGroup)
    {
        return $this->_createMediumGroup($majorGroup);
    }

    public function list()
    {
        $mediumGroups = MediumGroup::withCount('classRooms')->orderBy('updated_at','desc')->paginate(20);
        return view('admins.list_medium_groups', compact('mediumGroups'));
    }

    public function store(Request $request)
    {
        return $this->_store(null, $request);
    }
    public function storeWithMajorGroup(MajorGroup $majorGroup, Request $request)
    {
        return $this->_store($majorGroup, $request);
    }

    public function edit(MediumGroup $mediumGroup)
    {
        $majorGroups = MajorGroup::query()->where('is_active', '1')->orderBy('id', 'desc')->get();
        return view('admins.edit_medium_group', compact('mediumGroup', 'majorGroups'));
    }

    public function update(MediumGroup $mediumGroup, Request $request)
    {
//        Log::debug($request->all());
        $data = $request->validate([
            'name' => 'required|min:5|max:255',
            'description' => 'nullable|min:5|max:255',
            'is_active' => 'required',
            'details' =>    'nullable|min:10|max:2048',
            'major_group_id' => 'required|int'
        ],[
            'name' => '중분류 이름은 필수입니다.',
            'name.min' => '중분류는 2자 이상입니다.',
            'is_active' => '수업 활성화는 필수입니다.',
            'details.min' => '중분류 상세 설명은 10자 이상입니다.',
            'description.min' => '중분류 설명은 5자 이상입니다.',
            'major_group_id' => '대분류 선택은 필수입니다.',
        ]);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        $record = MediumGroup::findOrFail($mediumGroup->id);
//        $input = $request->all();
//        Log::debug($data);
        $record->fill($data)->save();
        session()->flash('success', 'MediumGroup saved successfully!');
        return redirect()->route('detailMediumGroup', $mediumGroup->id);
    }

    public function detail(MediumGroup $mediumGroup)
    {
        $sections = $mediumGroup->classRooms()->orderBy('updated_at', 'desc')->paginate(20);
        return view('admins.detail_medium_groups', compact('sections', 'mediumGroup'));
    }

    public function delete($id)
    {
        $mediumGroup = MediumGroup::findOrFail($id);
        $mediumGroup->delete();
        return redirect()->back()->withSuccess('MediumGroup with id: ' . $id . ' deleted successfully');
    }

    private function _createMediumGroup($majorGroup)
    {
        $majorGroups = null;
        if (!isset($majorGroup)) {
            $majorGroups = MajorGroup::query()->where('is_active', '1')->orderBy('id','desc')->get();
        }
        return view('admins.create_medium_group', compact('majorGroup', 'majorGroups'));
    }

    private function _store($majorGroup, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:2|max:255',
            'is_active' => 'required',
            'description' => 'nullable|min:5|max:255',
            'details' =>    'nullable|min:10|max:2048',
            'major_group_id' => 'nullable|int',
        ],[
            'name' => '중분류 이름은 필수입니다.',
            'name.min' => '중분류는 2자 이상입니다.',
            'is_active' => '중분류 공개 여부는 필수입니다.',
        ]);

        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        if (isset($majorGroup)) $data['major_group_id'] = $majorGroup->id;
        Log::debug($data);
        auth()->user()->mediumGroups()->createMany([$data]);
        return redirect()->route('listMediumGroups')->with('success', 'MediumGroup created successfully!');
    }
}
