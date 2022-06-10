<?php

namespace App\Http\Livewire;

use App\Models\Quiz;
use App\Models\Quote;
use App\Models\ClassRoom;
use App\Models\Section;
use Livewire\Component;
use App\Models\Question;
use App\Models\QuizHeader;
use Illuminate\Support\Facades\Log;

class UserQuizlv extends Component
{
    public $quote;
    public $quizid;
    public $classRooms;
    public $sections;
    public $count = 0;
    public $classRoomId;
    public $sectionId;
    public $quizSize = 1;
    public $quizPecentage;
    public $currentQuestion;
    public $setupQuiz = true;
    public $userAnswered = [];
    public $isDisabled = true;
    public $currectQuizAnswers;
    public $showResult = false;
    public $totalQuizQuestions;
    public $learningMode = false;
    public $quizInProgress = false;
    public $answeredQuestions = [];

    protected $rules = [
        'sectionId' => 'required',
        // 'quizSize' => 'required|numeric',
    ];


    public function showResults()
    {
        // Get a count of total number of quiz questions in Quiz table for the just finisned quiz.
        $this->totalQuizQuestions = Quiz::where('quiz_header_id', $this->quizid->id)->count();

        // Get a count of correctly answered questions for this quiz.
        $this->currectQuizAnswers = Quiz::where('quiz_header_id', $this->quizid->id)
            ->where('is_correct', '1')
            ->count();

        // Caclculate score for upding the quiz_header table before finishing the quid.
        $this->quizPecentage = round(($this->currectQuizAnswers / $this->totalQuizQuestions) * 100, 2);

        // Push all the question ids to quiz_header table to retreve them while displaying the quiz details
        $this->quizid->questions_taken = serialize($this->answeredQuestions);

        // Update the status of quiz as completed, this is used to resuming any uncompleted/abondened quizzes 
        $this->quizid->completed = true;

        // Insert the quiz score to quiz_header table
        $this->quizid->score = $this->quizPecentage;

        // Save the udpates.
        $this->quizid->save();

        // Hide quiz div and show result div wrapped in if statements in the blade template.
        $this->quizInProgress = false;
        $this->showResult = true;
    }
    public function render()
    {
        $this->sections = Section::withcount('questions')->where('is_active', '1')
            ->where('class_room_id', $this->classRoomId)
            ->orderBy('name')
            ->get();
        
        $this->classRooms = ClassRoom::withcount('sections')->where('is_active', '1')
            ->orderBy('name')->get();
        return view('livewire.user-quizlv');
    }

    public function updatedUserAnswered()
    {
        Log::debug("updatedUserAnswered : ".$this->userAnswered);
        if ($this->currentQuestion->type_id != 1) {
            # 주관식인 경우 
            if (empty(trim($this->userAnswered))) {
                $this->isDisabled = true;
            } else {
                $this->isDisabled = false;
            }
        } else {
            if ((empty($this->userAnswered) || (count($this->userAnswered) > 1))) {
                $this->isDisabled = true;
            } else {
                $this->isDisabled = false;
            }
        }
    }

    public function updatedClassRoomId()
    {
        Log::debug("updatedClassRoomId : ".$this->classRoomId);
        
    }

    public function mount()
    {
        $this->quote = Quote::inRandomOrder()->first();
    }

    public function getNextQuestion()
    {
        //Return a random question from the section selected by the user for quiz.
        // disabled because having issues with shuffle, it works but in a wierd way.

        // $question = Question::where('section_id', $this->sectionId)
        //     ->whereNotIn('id', $this->answeredQuestions)
        //     ->with(['answers' => function ($question) {
        //         $question->inRandomOrder();
        //     }])
        //     ->inRandomOrder()
        //     ->first();

        $question = Question::where('section_id', $this->sectionId)
            ->whereNotIn('id', $this->answeredQuestions)
            ->with('answers')
            ->inRandomOrder()
            ->first();

        //If the quiz size is greater then actual questions available in the quiz sections,
        //Finish the quiz and take the user to results page on exhausting all question from a given section.
        if ($question === null) {
            //Update quiz size to curret count as we have ran out of quesitons and forcing user to end the quiz ;)
            $this->quizid->quiz_size = $this->count - 1;
            $this->quizid->save();
            return $this->showResults();
        }
        //Update the questions taken array so that we don't repeat same question again in the quiz
        //We feed this array into whereNotIn chain in getNextquestion() function.
        array_push($this->answeredQuestions, $question->id);
        return $question;
    }

    public function startQuiz()
    {

        // Create a new quiz header in quiz_headers table and populate initial quiz information
        // Keep the instance in $this->quizid veriable for later updates to quiz.
        $this->validate();

        // 수업 리스트 선택 
        // 섹션 퀴즈의 전체 갯수를 항상 처리 
        Log::debug("startQuiz sectionId=".$this->sectionId);
        $this->quizSize = Question::query()->where('section_id', $this->sectionId)->where('is_active', '1')->count();
        Log::debug("startQuiz quizSize=".$this->quizSize);
        $this->quizid = QuizHeader::create([
            'user_id' => auth()->id(),
            'quiz_size' => $this->quizSize,
            'section_id' => $this->sectionId,
        ]);
        $this->count = 1;
        // Get the first/next question for the quiz.
        // Since we are using LiveWire component for quiz, the first quesiton and answers will be displayed through mount function.
        $this->currentQuestion = $this->getNextQuestion();
        $this->setupQuiz = false;
        $this->quizInProgress = true;
    }

    private function checkUserAnswer($questionAnswer)
    {
        // 파이썬으로 주관식 답 여부 체크 
        $userAnswered = preg_replace('/\s+/', '', $this->userAnswered);
        $answer = preg_replace('/\s+/', '', $questionAnswer);
        $result = ($userAnswered == $answer)? 1.0:0.0;

        if ($result === 0.0) {
            $cmd = sprintf('/home/ubuntu/venv/bin/python3 /home/ubuntu/dongwon/konlpy/check_answer.py "%s" "%s"', 
                            escapeshellarg($questionAnswer), 
                            escapeshellarg($this->userAnswered));
            Log::debug("cmd=".$cmd);
            $similar_score = shell_exec($cmd);
            Log::debug(trim($similar_score));
            $result = floatval($similar_score);
            // use Symfony\Component\Process\Process;
            // use Symfony\Component\Process\Exception\ProcessFailedException;

            // $process = new Process('sh /folder_name/file_name.sh');
            // $process->run();

            // // executes after the command finishes
            // if (!$process->isSuccessful()) {
            //     throw new ProcessFailedException($process);
            // }

            // echo $process->getOutput();
        }
        return $result;
    }

    public function nextQuestion()
    {
        Log::debug("nextQuestion : 정답 여부 체크");
        if ($this->currentQuestion->type_id == 1) {
            // 객관식에 대한 처리
            // Push all the question ids to quiz_header table to retreve them while displaying the quiz details
            $this->quizid->questions_taken = serialize($this->answeredQuestions);

            // Retrive the answer_id and value of answers clicked by the user and push them to Quiz table.
            list($answerId, $isChoiceCorrect) = explode(',', $this->userAnswered[0]);
            $userAnswered = $answerId;
        } if ($this->currentQuestion->type_id == 3) {
            // 주관식(영작) 문제 처리 
            $answerId = $this->currentQuestion->answers[0]->id;
            // 1. 문장내 공백은 한개씩만 유지
            $userAnswered = trim(preg_replace("/\s+/", " ", $this->userAnswered));
            // 2. 구분자를 중심으로 단어 분리
            $arrayUserAnswer = preg_split("/[,:.\s]/", strtolower($userAnswered));
            $arrayCorrentAnswer = preg_split("/[,:.\s]/", strtolower($this->currentQuestion->answers[0]->answer));
            // 3. 두배열 차이 비교 
            $answer_diff = ($arrayCorrentAnswer == $arrayUserAnswer); // array_diff($arrayCorrentAnswer, $arrayUserAnswer);
            $isChoiceCorrect = $answer_diff ? '1':'0';
        } else {
            // 주관식(번역)에 대한 처리를 해야만 함
            $answerId = $this->currentQuestion->answers[0]->id;
            
            // 주관식 정답 체크
            Log::debug("주관식 : user_answer=".$this->userAnswered.", answer=".$this->currentQuestion->answers[0]->answer);
            $resultScore = $this->checkUserAnswer($this->currentQuestion->answers[0]->answer);
            $isChoiceCorrect = ($resultScore >= 0.75) ? '1': (($resultScore >= 0.40) ? '2': '0');
            $userAnswered = $this->userAnswered;
        }

        // Insert the current question_id, answer_id and whether it is correnct or wrong to quiz table.
        Quiz::create([
            'user_id' => auth()->id(),
            'quiz_header_id' => $this->quizid->id,
            'section_id' => $this->currentQuestion->section_id,
            'question_id' => $this->currentQuestion->id,
            'answer_id' => $answerId,
            'user_answer' => $userAnswered,
            'is_correct' => $isChoiceCorrect
        ]);

        // Save the record
        $this->quizid->save();

        // Increment the quiz counter so we terminate the quiz on the number of question user has selected during quiz creation.
        $this->count++;

        // Reset the veriables for next question
        $answerId = '';
        $isChoiceCorrect = '';
        $this->reset('userAnswered');
        $this->isDisabled = true;

        // Finish the quiz when user has successfully taken all question in the quiz.
        if ($this->count == $this->quizSize + 1) {
            $this->showResults();
        }

        // Get a random questoin
        $this->currentQuestion = $this->getNextQuestion();
    }
}
