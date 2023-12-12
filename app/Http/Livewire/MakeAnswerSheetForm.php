<?php

namespace App\Http\Livewire;

use App\Constants\Question;
use App\Models\Answer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MakeAnswerSheetForm extends Component
{
    public $section;
    public $question_types;

    public $total_questions;
    public $retry_answer;  // 문제당 반복 횟수
    public $questions;

    public $flagExternalAnswerInputs;
    public $external_answers;

    public $question_start_no;

    public $rules = [];
    public function render()
    {
        return view('livewire.make-answer-sheet-form');
    }

    public function mount()
    {
        $this->total_questions = 10;
        $this->retry_answer = 5;
        $this->question_start_no = 0;
        $this->flagExternalAnswerInputs = false;
        $this->initializeQuestions();
    }

    public function boot()
    {

    }

    public function booted()
    {

    }

    public function changeSheets()
    {
        Log::debug(__METHOD__);
        Log::debug($this->total_questions);
        $this->initializeQuestions();

        if (count($this->questions) == 0) {
            for ($i = 0; $i < $this->total_questions; $i++) {
                $this->questions[] = [
                    "question_id" => null,
                    'question_type' => Question::ENGLISH_COMPOSITION_CLICK, // Question::SHORT_ANSWER,  //
                    'title' => "",
                    'answer' => [['answer_id'=>null, 'answer'=>""]],
                    'example' => explode("/", "I / am / a boy.")
                ];
            }
            $this->question_start_no = 0;
        }
    }

    private function initializeQuestions()
    {
        $this->questions = [];
        // DB에서 $this->section->id 의 quesiton 데이터를 읽어서 초기화 해야 함
        $questions = $this->section->questions()->where('is_active', '1')->orderBy('id', 'asc')->get();
        foreach ($questions as $question) {
            $this->retry_answer = $question->retry;
            $answers = $question->answers()->get();

            $this->questions[] = [
                'question_id' => $question->id,
                'question_type' =>  Question::ENGLISH_COMPOSITION_CLICK, // Question::SELECTIVE, // Question::SHORT_ANSWER,  //
                'title' => $question->question,
                'answer' => [
                    ["answer_id" => $answers[0]->id, "answer" =>$answers[0]->answer]
                ],
                'example' => explode('/', $answers[0]->answer),
                'retry' => $question->retry,
            ];

        }
        if (count($this->questions) > 0) {
            $this->total_questions = count($this->questions);
            $this->question_start_no = 1;

            Log::debug($this->questions);
        }
    }

    public function showExternAnswerInputs()
    {
        $this->flagExternalAnswerInputs = true;
    }

    public function hideExternAnswerInputs()
    {
        Log::debug(__METHOD__);
        $this->flagExternalAnswerInputs = false;
    }

    public function makeAnswerSheets()
    {
        Log::debug(__METHOD__);
        $this->questions = [];
        $this->external_answers = trim($this->external_answers);
        // $rows = explode('\n', $this->external_answers);
        $lines = preg_split("/\r?\n/", $this->external_answers);
        foreach ($lines as $index => $data) {
            $data = trim($data);
            Log::debug($data);
            $items = preg_split("/\*\*\*/", $data);
            if (count($items) !== 2 || (!is_numeric($items[0]))) continue;
            Log::debug($items);
            $title = intval($items[0]);
            $value = trim($items[1]);
            if (!isset($this->questions[$index])) {
                $this->questions[$index] = [
                    'question_id' => null,
                    'question_type' => Question::ENGLISH_COMPOSITION_CLICK,
                    'title' => $title,
                    'answer' => $value
                ];
            }
        }

        $this->total_questions = count($this->questions);
        $this->hideExternAnswerInputs();
    }

    public function saveQuestions()
    {
        try {
            DB::beginTransaction();
            // 기존 섹션에 연결 된 문제 데이터 삭제  -> 이미 문제를 풀었던 학생들의 경우 오류가 발생한다.
            // $this->section->questions()->delete();
            foreach ($this->questions as $no => $item) {
                $data = [
                    'question' => $item['title'],
                    'explanation' => $this->section->name,
                    'is_active' => '1',
                    'user_id' => Auth::id(),
                    'section_id' => $this->section->id,
                    'type_id' => ($item['question_type']+1),
                    'retry' => $this->retry_answer,
                ];
                $answer = trim(rtrim(trim($item['answer'][0]['answer']), '/'));

                if (isset($item['question_id']) && $item['question_id'] > 0) {
                    $question = \App\Models\Question::where('id',$item['question_id'])->update($data);
                    Answer::where('id', $item['answer'][0]['answer_id'])->update([
                        'answer' => $answer
                    ]);
                } else {
                    $question = \App\Models\Question::create($data);
                    $answers = [[
                        'is_checked' => '1',
                        'answer' => $answer
                    ]];
                    $status = $question->answers()->createMany($answers)->push();
                }
            }
            DB::commit();
            session()->flash('success', '영작나열형 문제지를 생성 했습니다.');
            return redirect()->route('detailSection', $this->section->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
        }
        // 오류 표시
    }

    public function deleteQuestion($index)
    {
        // DB 에서 데이터 삭제
        $question_id = $this->questions[$index]['question_id'];
        if (isset($question_id) && $question_id > 0) {
            \App\Models\Question::where('id', $question_id)->delete();
        }
        array_splice($this->questions, $index, 1);
    }

    public function showSampleShuffle($index)
    {
        $this->questions[$index]['example'] = explode("/", $this->questions[$index]['answer'][0]);
    }
}
