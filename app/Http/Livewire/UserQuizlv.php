<?php

namespace App\Http\Livewire;

use App\Models\Quiz;
use App\Models\Quote;
use App\Models\Section;
use Livewire\Component;
use App\Models\Question;
use App\Models\QuizHeader;
use Illuminate\Support\Facades\Log;

class UserQuizlv extends Component
{
    public $quote;
    public $quizid;
    public $sections;
    public $count = 0;
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
            ->orderBy('name')
            ->get();

        return view('livewire.user-quizlv');
    }

    public function updatedUserAnswered()
    {
        Log::debug("updatedUserAnswered : ".$this->userAnswered);
        if ($this->currentQuestion->type_id == 2) {
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
        } else {
            // 주관식에 대한 처리를 해야만 함
            $answerId = $this->currentQuestion->answers[0]->id;
            $userAnswered = $this->userAnswered;
            $isChoiceCorrect = ($userAnswered == $this->currentQuestion->answers[0]->answer)?'1':'0';
            // 파이썬으로 주관식 답 여부 체크 
            Log::debug("주관식 : user_answer=".$userAnswered.", answer=".$this->currentQuestion->answers[0]->answer);
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
