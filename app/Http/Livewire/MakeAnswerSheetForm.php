<?php

namespace App\Http\Livewire;

use App\Constants\Question;
use App\Models\Answer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class MakeAnswerSheetForm extends Component
{
    use WithFileUploads;

    public $section;
    public $question_types;
    public $is_modify = false;

    public $total_questions;
    public $retry_answer;  // 문제당 반복 횟수
    public $questions;

    public $question_start_no;

    public $csv_file;

    public $rules = [

    ];
    public function render()
    {
        return view('livewire.make-answer-sheet-form');
    }

    public function mount()
    {
        $this->total_questions = 10;
        $this->retry_answer = 5;
        $this->question_start_no = 0;
        $this->initializeQuestions();
        $this->total_questions = (count($this->questions)== 0) ? 10 : count($this->questions);
    }

    public function boot()
    {

    }

    public function booted()
    {

    }

    public function parseFile()
    {
        $this->validate(['csv_file' => 'file|mimes:csv,txt']);
        // session()->flash('error', 'csv 파일만 업로드 가능합니다.');
        $name = $this->csv_file->getClientOriginalName();
        Log::debug($name);
        $path = $this->csv_file->getRealPath();
        Log::debug($path);
        $data = array_map('str_getcsv', file($path));
        $this->total_questions = count($data)-1;

        $this->questions = [];
        foreach ($data as $row => $item) {
            if ($row == 0) continue; // 헤더는 무시
            if ($row == 1) $this->question_start_no = $item[0];
            if (count($item) <= 3) {
                Log::debug("{$item[0]}번\n\t {$item[1]}\n\t {$item[2]}");
                $this->questions[] = [
                    "question_id" => null,
                    "question_no" => ($item[0] != '') ? $item[0] : null,
                    'question_type' => Question::ENGLISH_COMPOSITION_CLICK,
                    'answer' => [['answer_id'=>null, 'answer'=>$item[2]]],
                    'title' => $item[1],
                    'example' => explode("/", "I / am / a boy.")
                ];
            } else {
                Log::debug(json_encode($item));
            }
        }

    }


    public function changeSheets()
    {
        Log::debug(__METHOD__);
        Log::debug($this->total_questions);
        $this->initializeQuestions();

        if (count($this->questions) < $this->total_questions) {
            $start_no_question = (count($this->questions)==0) ? 1 : $this->questions[0]['question_no'];
            for ($i = count($this->questions); $i < $this->total_questions; $i++) {
                $this->questions[] = [
                    "question_id" => null,
                    'question_no' => $start_no_question + ($i),
                    'question_type' => Question::ENGLISH_COMPOSITION_CLICK,
                    'answer' => [['answer_id'=>null, 'answer'=>""]],
                    'title' => "",
                    'example' => explode("/", "I / am / a boy.")
                ];
            }
        }

        $this->total_questions = count($this->questions);
        $this->question_start_no = $this->questions[0]['question_no'];
        Log::debug($this->questions);
    }

    private function initializeQuestions()
    {
        $this->questions = [];
        // DB에서 $this->section->id 의 quesiton 데이터를 읽어서 초기화 해야 함
        $questions = $this->section->questions()->where('is_active', '1')->orderBy('id', 'asc')->get();
        foreach ($questions as $index => $question) {
            $this->retry_answer = $question->retry;
            $answers = $question->answers()->get();

            $this->questions[] = [
                'question_id' => $question->id,
                'question_no' => (isset($question->question_no)) ? $question->question_no : ($index+1),
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

            $this->question_start_no = $this->questions[0]['question_no'];

            Log::debug($this->questions);
            $this->is_modify = true;
        }
    }

    public function updateQuestionStartNo()
    {
        Log::debug(__METHOD__);
        Log::debug($this->question_start_no);
        foreach($this->questions as $index => $question) {
            $this->questions[$index]['question_no'] = $this->question_start_no + $index;
        }
        Log::debug($this->questions);
    }
//    public function showExternAnswerInputs()
//    {
//        $this->flagExternalAnswerInputs = true;
//    }

//    public function hideExternAnswerInputs()
//    {
//        Log::debug(__METHOD__);
//        $this->flagExternalAnswerInputs = false;
//    }

//    public function makeAnswerSheets()
//    {
//        Log::debug(__METHOD__);
//        $this->questions = [];
//        $this->external_answers = trim($this->external_answers);
//        // $rows = explode('\n', $this->external_answers);
//        $lines = preg_split("/\r?\n/", $this->external_answers);
//        foreach ($lines as $index => $data) {
//            $data = trim($data);
//            Log::debug($data);
//            $items = preg_split("/\*\*\*/", $data);
//            if (count($items) !== 2 || (!is_numeric($items[0]))) continue;
//            Log::debug($items);
//            $title = intval($items[0]);
//            $value = trim($items[1]);
//            if (!isset($this->questions[$index])) {
//                $this->questions[$index] = [
//                    'question_id' => null,
//                    'question_type' => Question::ENGLISH_COMPOSITION_CLICK,
//                    'title' => $title,
//                    'answer' => $value
//                ];
//            }
//        }
//
//        $this->total_questions = count($this->questions);
//        $this->hideExternAnswerInputs();
//    }

    public function saveQuestions()
    {
        Log::debug(__METHOD__);
        try {
            DB::beginTransaction();
            // 기존 섹션에 연결 된 문제 데이터 삭제  -> 이미 문제를 풀었던 학생들의 경우 오류가 발생한다.
            // $this->section->questions()->delete();
            foreach ($this->questions as $no => $item) {
                $data = [
                    'question_no' => $item['question_no'],
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
