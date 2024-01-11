<?php

namespace App\Http\Controllers;

use App\Models\MajorGroup;
use Illuminate\Http\Request;

class MajorGroupController extends Controller
{
    public function create()
    {
        return view('admins.create_major_group');
    }

    public function list()
    {
        $majorGroups = MajorGroup::withCount('mediumGroups')->orderBy('updated_at','desc')->paginate(20);
        return view('admins.list_major_groups', compact('majorGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'is_active' => 'required',
            'description' => 'nullable|max:255',
            'details' =>    'nullable|max:2048',
        ],[
            'name' => '과정 이름은 필수입니다.',
            'is_active' => '과정 활성화는 필수입니다.',
        ]);

        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        auth()->user()->majorGroups()->createMany([$data]);
        return redirect()->route('listMajorGroups')->with('success', 'MajorGroup created successfully!');
    }

    public function edit(MajorGroup $majorGroup)
    {
        return view('admins.edit_major_group', compact('majorGroup'));
    }

    public function update(MajorGroup $majorGroup, Request $request)
    {
//        Log::debug($request->all());
        $data = $request->validate([
            'name' => 'required|min:2|max:255',
            'description' => 'nullable|max:255',
            'is_active' => 'required',
            'details' =>    'nullable|max:1024',
        ],[
            'name' => '과정 이름은 필수입니다.',
            'name.min' => '과정 이름은 2자 이상입니다.',
            'is_active' => '과정 활성화는 필수입니다.',
        ]);
        if (!isset($data['description'])) {
            $data['description'] = '';
        }
        if (!isset($data['details'])) {
            $data['details'] = '';
        }
        $record = MajorGroup::findOrFail($majorGroup->id);
//        $input = $request->all();
//        Log::debug($data);
        $record->fill($data)->save();
        session()->flash('success', 'MajorGroup saved successfully!');
        return redirect()->route('detailMajorGroup', $majorGroup->id);
    }

    public function detail(MajorGroup $majorGroup)
    {
        $sections = $majorGroup->mediumGroups()->paginate(20);
        return view('admins.detail_major_groups', compact('sections', 'majorGroup'));
    }

    public function delete($id)
    {
        $majorGroup = MajorGroup::findOrFail($id);
        $majorGroup->delete();
        return redirect()->back()->withSuccess('MajorGroup with id: ' . $id . ' deleted successfully');
    }
}
