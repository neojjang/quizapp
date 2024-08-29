<?php

namespace App\Http\Livewire;

use App\Constants\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class MakeListeningTestForm extends Component
{
    use WithFileUploads;

    public $section;
    public $question_types;

    public $total_questions;
    public $questions;

    // 듣기평가 mp3 파일
    public $mp3File;
    public $tempUrl;
    public $uploading = false;

    public $external_answers;

    public $flagSetQuestionStartNo;
    public $question_start_no;

    protected $rules = [
        'mp3File' => 'required|file|mimes:mp3|max:20480', // 10MB Max

    ];

    public function render()
    {
        return view('livewire.make-listening-test-form');
    }

    public function mount()
    {Log::info("mount()");
        $this->total_questions = 10;
        $this->question_start_no = 0;
        $this->flagSetQuestionStartNo = false;
        $this->mp3File = null;
        $this->initializeQuestions();
    }

    public function boot()
    {
        Log::info("boot()");
    }

    public function booted()
    {
        Log::info("booted()");
    }

    public function updated($propertyName)
    {
        Log::info("updated !@!@#!~@#!@#    ".$propertyName);
    }

    public function updatedMp3File()
    {
        Log::info('updatedMp3File called');
        Log::info($this->mp3File);
        $this->validateOnly('mp3File');

        $this->tempUrl = $this->mp3File->temporaryUrl();
    }
//    public function upload()
//    {
//        Log::info("upload");
//        $this->validate([
//            'mp3File' => 'required|file|mimes:mp3|max:102400000'
//        ], [
//            'mp3File.mimes' => 'The file must be a file of type: audio/mpeg.',
//            'mp3File.required' => 'The file is required.',
//
//        ]);
//
//        $filename = time().'.'.$this->mp3File->extension();
////        $url = Storage::disk('s3')->put('mp3-uploads', $this->mp3File);
////
////        UploadedMP3::create([
////            'file_name' => $fileName,
////            'file_url' => $url
////        ]);
//
//        session()->flash('success', 'MP3 file uploaded successfully.');
//        $this->reset('mp3File');
//    }
    public function cancelUploadFile()
    {
        Log::info("delete file path=".$this->mp3File->path());
        File::delete($this->mp3File->path());
//        $this->mp3File = null;
        $this->reset('mp3File');
    }


    public function changeSheets()
    {
        Log::debug(__METHOD__);
        Log::debug($this->total_questions);
        $this->initializeQuestions();

        if (count($this->questions) == 0) {
            for ($i = 0; $i < $this->total_questions; $i++) {
                $this->questions[] = [
                    'question_type' => Question::SELECTIVE, // Question::SHORT_ANSWER,  //
                    'title' => sprintf("%d번 문제", ($i+1)),
                    'answer' => [false,false,false,false,false]
                ];
            }
            $this->question_start_no = 0;
        }
    }

    public function changeQuestionType($index, $question_type)
    {
        Log::debug("{__METHOD__} : {$index}, {$question_type}");
        Log::debug($this->questions[$index]);
        $this->questions[$index]['question_type'] = $question_type;
        if ($question_type == Question::SELECTIVE) {
            $this->questions[$index]['answer'] = [false,false,false,false,false];
        } else {
            $this->questions[$index]['answer'] = [''];
        }

    }

    private function initializeQuestions()
    {
        $this->questions = [];
        // DB에서 $this->section->id 의 quesiton 데이터를 읽어서 초기화 해야 함
        $questions = $this->section->questions()->where('is_active', '1')->orderBy('id', 'asc')->get();
        foreach ($questions as $question) {
            $answers = $question->answers()->get();
            if (($question->type_id-1)== Question::SELECTIVE) {
                $answer_data = [];
                for ($i = 0; $i < count($answers); $i++) {
                    $answer_data[$i] = ($answers[$i]->is_checked=='1');
                }
            } else {
                $answer_data = [$answers[0]->answer];
            }
            $this->questions[] = [
                'question_type' =>  ($question->type_id-1), // Question::SELECTIVE, // Question::SHORT_ANSWER,  //
                'title' => $question->question,
                'answer' => $answer_data
            ];

        }
        if (count($this->questions) > 0) {
            $this->total_questions = count($this->questions);
            $this->question_start_no = intval($this->questions[0]['title']);

            Log::debug($this->questions);
        }
    }

    public function makeAnswerSheets()
    {
        Log::debug(__METHOD__);
        $this->questions = [];
        $this->external_answers = trim($this->external_answers);
        // $rows = explode('\n', $this->external_answers);
        $lines = preg_split("/\r?\n/", $this->external_answers);
        foreach ($lines as $data) {
            $data = trim($data);
            Log::debug($data);
            $items = preg_split("/\./", $data);
            if (count($items) !== 2 || (!is_numeric($items[0]))) continue;
            Log::debug($items);
            $no = intval($items[0]);
            $value = trim($items[1]);
            if (!isset($this->questions[($no-1)])) {
                $this->questions[($no-1)]['question_type'] = (is_numeric($value))? Question::SELECTIVE:Question::SHORT_ANSWER;
                $this->questions[($no-1)]['title'] =  sprintf("%d번 문제", ($no));
                $this->questions[($no-1)]['answer'] = $value;
            }
        }

        $this->total_questions = count($this->questions);
        $this->hideExternAnswerInputs();
    }

    public function saveQuestions()
    {
        try {
            DB::beginTransaction();
            $this->section->questions()->delete();
            foreach ($this->questions as $no => $item) {
                $question = \App\Models\Question::create([
                    'question' => $item['title'],
                    'explanation' => $this->section->name,
                    'is_active' => '1',
                    'user_id' => Auth::id(),
                    'section_id' => $this->section->id,
                    'type_id' => ($item['question_type']+1)
                ]);
                $answers = [];
                if ($item['question_type'] == Question::SELECTIVE) {
                    for ($i=0; $i < 5; $i++) {
                        $answers[] = [
                            'is_checked' => ($item['answer'][$i])? '1': '0',
                            'answer' => ($i+1)
                        ];
                    }
                } else {
                    $answers[] = [
                        'is_checked' => '1',
                        'answer' => $item['answer'][0]
                    ];
                }

                $status = $question->answers()->createMany($answers)->push();
            }
            DB::commit();
            session()->flash('success', 'OMR 답안지를 생성 했습니다.');
            return redirect()->route('detailSection', $this->section->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
        }
        // 오류 표시
    }

    public function deleteQuestion($index)
    {
        array_splice($this->questions, $index, 1);
    }

    public function rearrangeQuestionNo()
    {
        for ($i = 0; $i < count($this->questions); $i++) {
            $this->questions[$i]['title'] = sprintf("%d번 문제", ($i+1));
        }
    }

    public function showSetQuestionStartNo($flag)
    {
        $this->flagSetQuestionStartNo = $flag;
    }

    public function setQuestionStartNo()
    {
        for ($i=0; $i < count($this->questions); $i++) {
            $this->questions[$i]['title'] = sprintf("%d번 문제", ($this->question_start_no+$i));
        }
        $this->flagSetQuestionStartNo = false;
    }

}
