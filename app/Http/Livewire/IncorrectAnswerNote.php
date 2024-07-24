<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\AnswerNoteTestPaper;
use App\Models\IncorrectAnswerNote;

class IncorrectAnswerNoteComponent extends Component
{
    public $problemId;
    public $content = '';
    public $problem;

//    public function mount($problemId)
//    {
//        $this->problemId = $problemId;
//        $this->problem = Problem::findOrFail($problemId);
//        $existingNote = IncorrectAnswerNote::where('problem_id', $this->problemId)
//            ->where('user_id', auth()->id())
//            ->first();
//
//        if ($existingNote) {
//            $this->content = $existingNote->content;
//        }
//    }
//
//    public function save()
//    {
//        $this->validate([
//            'content' => 'required',
//        ]);
//
//        IncorrectAnswerNote::updateOrCreate(
//            [
//                'problem_id' => $this->problemId,
//                'user_id' => auth()->id(),
//            ],
//            ['content' => $this->content]
//        );
//
//        session()->flash('message', '오답노트가 저장되었습니다.');
//    }
//
    public function render()
    {
        return view('livewire.incorrect-answer-note');
    }
}



