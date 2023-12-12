<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuizHeader;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserAnswer extends Component
{
    public $choice;
    public Quiz $userAnswer;
    public Question $question;
    public QuizHeader $quizHeader;

    // public $isCorrect;
    // public $isWrong;
    // public $isHold;

    public function mount()
    {
        $this->choice = collect(['A', 'B', 'C', 'D', 'E']);
        // $this->isCorrect = (bool)($this->userAnswer->is_correct === '1');
        // $this->isWrong = (bool)($this->userAnswer->is_correct === '0');
        // $this->isHold = (bool)($this->userAnswer->is_correct === '2');
        if (($this->question->type_id -1)== \App\Constants\Question::SELECTIVE) {
            $this->userAnswer->user_answer = explode(',', $this->userAnswer->user_answer);
        }
    }

    public function render()
    {
        return view('livewire.user-answer');
    }

    public function updaing($field, $value)
    {
        Log::debug("updating field={$field}, value={$value}");
    }

    public function changeUserAnswer($value)
    {
        // Log::debug("changeUserAnswer value={$value}");
        $this->userAnswer->is_correct = $value;
        $this->userAnswer->save();

        // 정답 채점인 경우 점수 계산
        $currectQuizAnswers = Quiz::where('quiz_header_id', $this->quizHeader->id)
            ->where('is_correct', '1')
            ->count();
        $totalQuizQuestions = Quiz::where('quiz_header_id', $this->quizHeader->id)->count();

        // Caclculate score for upding the quiz_header table before finishing the quid.
        $quizPecentage = round(($currectQuizAnswers / $totalQuizQuestions) * 100, 2);

        $this->quizHeader->score = $quizPecentage;
        $this->quizHeader->save();
    }
}
