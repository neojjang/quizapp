<?php

namespace App\Http\Controllers;

use App\Models\AnswerNoteTestPaper;
use Illuminate\Http\Request;

class AnswerNoteController extends Controller
{
    // 관리자
    // 오답노트 문제 리스트
    public function list()
    {
        // 오답노트 문제지. 작성한 유저수를 포함 해야 한다.
        $answerNotes = AnswerNoteTestPaper::withCount('mediumGroups')->orderBy('updated_at','desc')->paginate(20);
        return view('admins.list_major_groups', compact('answerNotes'));
    }
    // 오답노트 문제지 등록

    

    // 학생
    // 작성
    // 학생의 오답노트 리스트

}
