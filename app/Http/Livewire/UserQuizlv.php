<?php

namespace App\Http\Livewire;

use App\Models\Quiz;
use App\Models\Quote;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\SectionFiles;
use Illuminate\Support\Arr;
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
    public $classRoomId = 0;
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

    public $classRoomName;
    public $sectionName;
    public $sectionTypeId = \App\Constants\Section::NORMAL;
    public $questions;
    public $mp3File;
    public $omrAnswered = [];

    public $currentExample;
    public $selectedOrder = 0;
    public $retryCount = 0;
    public $showRetry = false;
    public $extraInfo = null;

    public $currentTimer = 0;
    public $isTimeout = false;

    protected $queryString = ['sectionId'];

    protected $rules = [
        'sectionId' => 'required',
        // 'quizSize' => 'required|numeric',
    ];

    public function showResults()
    {
        Log::debug(__METHOD__);
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
        Log::debug(__METHOD__);
        $sections = Section::withcount('questions')->where('is_active', '1')
            ->where('class_room_id', $this->classRoomId)
            ->orderBy('name')
            ->get();
        // 참여한 시험인지 확인
        $this->sections = collect($sections)->transform(function ($section) {
//            Log::debug($section->name);
            $quizHeader = $section->quizHeaders()->where('user_id', auth()->id())->where('completed', 1)->first();
//            Log::debug($quizHeader);
            $section['been_taken'] = isset($quizHeader);
            return $section;
        });
        if ($this->classRoomId === 0) {
            $this->classRooms = ClassRoom::withcount('sections')->where('is_active', '1')
                ->orderBy('name')->get();
        }
        return view('livewire.user-quizlv');
    }

    public function updatedUserAnswered()
    {
        Log::debug(__METHOD__);
        Log::debug($this->userAnswered);
        if ($this->currentQuestion->type_id != \App\Constants\Question::SELECTIVE) {
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

    public function mount($major_group, $medium_group, $class_room)
    {
        Log::debug(__METHOD__);
//        Log::debug($major_group->name);
//        Log::debug($medium_group->name);
//        Log::debug($class_room);
        if (isset($class_room)) {
            $this->classRoomId = $class_room->id;
            $this->classRoomName = $class_room->name;
        }
        $this->quote = Quote::inRandomOrder()->first();
    }

    public function getNextQuestion()
    {
        Log::debug(__METHOD__);
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
            ->orderBy('id', 'asc') // isRandomOrder()
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
        $section = Section::findOrFail($this->sectionId);
        $this->classRoomName = $section->class_room->name;
        $this->sectionName = $section->name;
        $this->sectionTypeId = $section->type_id;
        if (\App\Constants\Section::isSectionType($this->sectionTypeId)) {
            switch($this->sectionTypeId) {
                case \App\Constants\Section::NORMAL:
                    $this->startNormalQuiz();
                    break;
                case \App\Constants\Section::OMR:
                    $this->startOMRQuiz();
                    break;
                case \App\Constants\Section::ENGLISH_COMPOSITION_CLICK:
                    $this->startEnglishCompositionClick();
                    break;
                case \App\Constants\Section::LISTENING_TEST:
                    $this->startListeningTestQuiz();
                    break;
                default:
                    Log::debug("Error Section Type :".$this->sectionTypeId);
                    break;
            }

        }
    }

    private function checkUserAnswer($questionAnswer, $userAnswered)
    {
        // 파이썬으로 주관식 답 여부 체크
//        $userAnswered = preg_replace('/\s+/', '', $this->userAnswered);
        $trim_userAnswered = preg_replace('/\s+/', '', $userAnswered);
        $answer = preg_replace('/\s+/', '', $questionAnswer);
        $result = ($trim_userAnswered == $answer)? 1.0:0.0;

        // 정답안의 길이가 5자 이상의 경우만 분석기 비교 실행
        if ($result === 0.0 && mb_strlen($trim_userAnswered) > 10) {
            $cmd = sprintf('/home/ubuntu/venv/bin/python3 /home/ubuntu/dongwon/konlpy/check_answer.py "%s" "%s"',
                            escapeshellarg($questionAnswer),
                            escapeshellarg($userAnswered));
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

    /**
     * 영작 문제 검사
     * @param $question
     * @param $userAnswered
     * @return array
     */
    public function checkWritingAnswer($question, $userAnswered)
    {
        $answerId = $question->answers[0]->id;
        // 1. 문장내 공백은 한개씩만 유지
        $userAnswered = trim(preg_replace("/\s+/", " ", $userAnswered));
        // 2. 구분자를 중심으로 단어 분리
        $arrayUserAnswer = preg_split("/[,:.\s]/", strtolower($userAnswered));
        $arrayCorrentAnswer = preg_split("/[,:.\s]/", strtolower($question->answers[0]->answer));
        // 3. 두배열 차이 비교
        $answer_diff = ($arrayCorrentAnswer == $arrayUserAnswer); // array_diff($arrayCorrentAnswer, $arrayUserAnswer);
        $isChoiceCorrect = $answer_diff ? '1':'0';

        return [
            'answerId' => $answerId,
            'userAnswered' => $userAnswered,
            'isChoiceCorrect' => $isChoiceCorrect
        ];
    }

    /**
     * 번역 문제 검사
     * @param $question
     * @param $userAnswered
     * @return void
     */
    public function checkTranslatedAnswer($question, $userAnswered)
    {
        $answerId = $question->answers[0]->id;

        // 주관식 정답 체크
        Log::debug("주관식 : user_answer=".$userAnswered.", answer=".$question->answers[0]->answer);
        $resultScore = $this->checkUserAnswer($question->answers[0]->answer, $userAnswered);
        $isChoiceCorrect = ($resultScore >= 0.75) ? '1': (($resultScore >= 0.40) ? '2': '0');
//        $userAnswered = $this->userAnswered;

        return [
            'answerId' => $answerId,
            'userAnswered' => $userAnswered,
            'isChoiceCorrect' => $isChoiceCorrect
        ];
    }

    /**
     * 객관식 문제 검사
     * @param $question
     * @param $userAnswered
     * @return array
     */
    public function checkChoiceAnswer($question, $userAnswered)
    {
        // 정답 갯수
        $correctCount = $question->answers()->where('is_checked', '=', '1')->count();
        Log::debug("{$question->id}, correctCount={$correctCount}");

        $answerIds = [];
        $userFirstAnswerId = 0;
        $userCorrectCount = 0;
        foreach ($userAnswered as $index => $value) {
            list($answerId, $isChoiceCorrect) = explode(',', $value);
            if ($isChoiceCorrect == '1') $userCorrectCount += 1;
            else $userCorrectCount -= 1;
            if ($userFirstAnswerId == 0) $userFirstAnswerId = $answerId;
            array_push($answerIds, $answerId);
        }
        // Retrive the answer_id and value of answers clicked by the user and push them to Quiz table.
        // list($answerId, $isChoiceCorrect) = explode(',', $userAnswered[0]);
        // $userAnswered = $answerId;

        return [
            'answerId' => $userFirstAnswerId,
            'userAnswered' => implode(",",$answerIds),
            'isChoiceCorrect' => ($correctCount == $userCorrectCount) ? '1':'0'
        ];
    }

    public function checkCurrentAnswer()
    {
        Log::debug(__METHOD__);
        $currentQuestionTypeId = ($this->currentQuestion->type_id > 0) ? ($this->currentQuestion->type_id-1) : \App\Constants\Question::SELECTIVE;
        if ($currentQuestionTypeId == \App\Constants\Question::SELECTIVE) {
            // 객관식에 대한 처리
            // Push all the question ids to quiz_header table to retreve them while displaying the quiz details
            $this->quizid->questions_taken = serialize($this->answeredQuestions);

            return $this->checkChoiceAnswer($this->currentQuestion, [$this->userAnswered]);
//            // Retrive the answer_id and value of answers clicked by the user and push them to Quiz table.
//            list($answerId, $isChoiceCorrect) = explode(',', $this->userAnswered[0]);
//            $userAnswered = $answerId;
        } else if ($currentQuestionTypeId == \App\Constants\Question::ENGLISH_COMPOSITION) {
            // 주관식(영작) 문제 처리
            return $this->checkWritingAnswer($this->currentQuestion, $this->userAnswered);
//            $answerId = $this->currentQuestion->answers[0]->id;
//            // 1. 문장내 공백은 한개씩만 유지
//            $userAnswered = trim(preg_replace("/\s+/", " ", $this->userAnswered));
//            // 2. 구분자를 중심으로 단어 분리
//            $arrayUserAnswer = preg_split("/[,:.\s]/", strtolower($userAnswered));
//            $arrayCorrentAnswer = preg_split("/[,:.\s]/", strtolower($this->currentQuestion->answers[0]->answer));
//            // 3. 두배열 차이 비교
//            $answer_diff = ($arrayCorrentAnswer == $arrayUserAnswer); // array_diff($arrayCorrentAnswer, $arrayUserAnswer);
//            $isChoiceCorrect = $answer_diff ? '1':'0';
        } else if ($currentQuestionTypeId == \App\Constants\Question::TRANSLATION) {
            // 주관식(번역)에 대한 처리를 해야만 함
            return $this->checkTranslatedAnswer($this->currentQuestion, $this->userAnswered);
//            $answerId = $this->currentQuestion->answers[0]->id;
//
//            // 주관식 정답 체크
//            Log::debug("주관식 : user_answer=".$this->userAnswered.", answer=".$this->currentQuestion->answers[0]->answer);
//            $resultScore = $this->checkUserAnswer($this->currentQuestion->answers[0]->answer);
//            $isChoiceCorrect = ($resultScore >= 0.75) ? '1': (($resultScore >= 0.40) ? '2': '0');
//            $userAnswered = $this->userAnswered;
        } else if ($currentQuestionTypeId == \App\Constants\Question::ENGLISH_COMPOSITION_CLICK) {
            $isChoiceCorrect = true;
            $userAnswered = "";
            foreach ($this->userAnswered as $index => $item) {
                $isChoiceCorrect = $isChoiceCorrect & $item[1];
                $userAnswered = $userAnswered . (($index == 0) ? trim($item[0]) : " / ".trim($item[0]));
            }
            $this->extraInfo[] = $this->userAnswered;

            return [
                'answerId' => $this->currentQuestion->answers[0]->id,
                'userAnswered' => $userAnswered,
                'isChoiceCorrect' => $isChoiceCorrect ? '1':'0'
            ];
        }

        return null;
    }

    public function nextQuestion()
    {
        Log::debug("nextQuestion : 정답 여부 체크");
        // OMR 타입은 이 함수를 처리 하지 않기 때문에 비교가 필요없음
//        if ($this->sectionTypeId != \App\Constants\Section::OMR) {
//
//        }
        $result = $this->checkCurrentAnswer();

        // Insert the current question_id, answer_id and whether it is correnct or wrong to quiz table.
        Quiz::create([
            'user_id' => auth()->id(),
            'quiz_header_id' => $this->quizid->id,
            'section_id' => $this->currentQuestion->section_id,
            'question_id' => $this->currentQuestion->id,
            'answer_id' => $result['answerId'],
            'user_answer' => $result['userAnswered'],
            'is_correct' => $result['isChoiceCorrect'],
            'retry' => ($this->retryCount+1),
            'extra_info' => (isset($this->extraInfo)) ? json_encode($this->extraInfo) : $this->extraInfo
        ]);

        // Save the record
        $this->quizid->save();

        // Increment the quiz counter so we terminate the quiz on the number of question user has selected during quiz creation.
        $this->count++;
        $this->retryCount = 0;

        // Reset the veriables for next question
        $answerId = '';
        $isChoiceCorrect = '';
        $this->reset('userAnswered');
        $this->isDisabled = true;

        // Finish the quiz when user has successfully taken all question in the quiz.
        if ($this->count == $this->quizSize + 1) {
            $this->showResults();
        } else {
            // Get the first/next question for the quiz.
            // Since we are using LiveWire component for quiz, the first quesiton and answers will be displayed through mount function.
            $this->currentQuestion = $this->getNextQuestion();
            // 현재 타이머 설정
            $this->currentTimer = $this->currentQuestion->timer;
            if (($this->currentQuestion->type_id - 1) == \App\Constants\Question::ENGLISH_COMPOSITION_CLICK) {
                $this->reset('currentExample');
                $this->retryCount = 0;
                // 구문 선택 순서
                $this->selectedOrder = 0;
                $this->extraInfo = [];

                // 구문 섞기
                // $this->currentExample = explode('/', $this->currentQuestion->answers[0]->answer);
                // shuffle($this->currentExample);
                $this->makeShuffledExample();
            }
            Log::debug("call timerRestart event");
            $this->emit('timerRestart');
        }
    }

    /**
     * 일반 문제 유형 진행.
     * @return void
     */
    public function startNormalQuiz()
    {
        Log::debug(__METHOD__);
        $this->reset('omrAnswered');

        $this->quizSize = Question::query()->where('section_id', $this->sectionId)->where('is_active', '1')->count();
        Log::debug("startQuiz quizSize=" . $this->quizSize);
        $this->quizid = QuizHeader::create([
            'user_id' => auth()->id(),
            'quiz_size' => $this->quizSize,
            'section_id' => $this->sectionId,
        ]);
        $this->count = 1;
        $this->retryCount = 0;
        // Get the first/next question for the quiz.
        // Since we are using LiveWire component for quiz, the first quesiton and answers will be displayed through mount function.
        $this->currentQuestion = $this->getNextQuestion();
        $this->setupQuiz = false;
        $this->quizInProgress = true;
        $this->currentTimer = 0;
    }

    /**
     * OMR 유형 진행
     * @return void
     */
    public function startOMRQuiz()
    {
        Log::debug(__METHOD__);
        $this->reset('userAnswered');

        $this->quizSize = Question::query()->where('section_id', $this->sectionId)->where('is_active', '1')->count();
        Log::debug("startQuiz quizSize=" . $this->quizSize);
        $this->quizid = QuizHeader::create([
            'user_id' => auth()->id(),
            'quiz_size' => $this->quizSize,
            'section_id' => $this->sectionId,
        ]);
        $this->count = 1;
        // 사용자 입력 답안지 초기화
//        for ($i=0; $i < $this->quizSize; $i++) {
//            $this->omrAnswered[$i] = [];
//        }
        // Get the first/next question for the quiz.
        // Since we are using LiveWire component for quiz, the first quesiton and answers will be displayed through mount function.
        $this->questions = $this->getAllQuestions();

        $this->setupQuiz = false;
        $this->quizInProgress = true;
        $this->currentTimer = 0;
    }

    public function startListeningTestQuiz()
    {
        Log::debug(__METHOD__);
        $this->reset('userAnswered');

        $this->quizSize = Question::query()->where('section_id', $this->sectionId)->where('is_active', '1')->count();
        Log::debug("startQuiz quizSize=" . $this->quizSize);
        $this->quizid = QuizHeader::create([
            'user_id' => auth()->id(),
            'quiz_size' => $this->quizSize,
            'section_id' => $this->sectionId,
        ]);
        $this->count = 1;

        // Get the first/next question for the quiz.
        // Since we are using LiveWire component for quiz, the first quesiton and answers will be displayed through mount function.
        $this->questions = $this->getAllQuestions();
        $this->mp3File = $this->getListeningFile();

        $this->setupQuiz = false;
        $this->quizInProgress = true;
        $this->currentTimer = 0;
    }

    private function getAllQuestions()
    {
        Log::debug(__METHOD__);
        $questions = Question::where('section_id', $this->sectionId)
            ->with('answers')
            ->orderBy('id', 'asc')
            ->get();
        return $questions;
    }

    public function updatingOmrAnswered($value, $idx)
    {
        Log::debug(__METHOD__." idx=".$idx);
        Log::debug($value);
    }
    public function updatedOmrAnswered($value, $idx)
    {
        Log::debug(__METHOD__." idx=".$idx);
        // clear unselected values
        foreach ($this->omrAnswered as $key => $omrAnswered) {
            $this->omrAnswered[$key] = Arr::where($omrAnswered, function ($value, $no) {
                return $value !== false;
            });
            if (count($this->omrAnswered[$key]) == 0) {
                unset($this->omrAnswered[$key]);
            }
        }
        Log::debug($this->omrAnswered);
        Log::debug("omr quizSize=" . $this->quizSize. ", answeredSize=".count($this->omrAnswered));
        if (count($this->omrAnswered) < $this->quizSize) {
            $this->isDisabled = true;
        } else {
            $this->isDisabled = false;
        }
        Log::debug('isDisabled='.$this->isDisabled);
    }

    public function checkAllAnswers()
    {
        Log::debug(__METHOD__);

        foreach ($this->questions as $idx => $question) {
            $userAnswered = $this->omrAnswered[$idx];

            if ($question->type_id == 1) {
                // 객관식에 대한 처리
                $result = $this->checkChoiceAnswer($question, $userAnswered);
            } elseif ($question->type_id == 4) {
                // 단답형 처리
                $result = $this->checkShortAnswer($question, $userAnswered[0]);
            } elseif ($question->type_id == 3) {
                // 주관식(영작) 문제 처리
                $result = $this->checkWritingAnswer($question, $userAnswered[0]);
            } else {
                // 주관식(번역)에 대한 처리를 해야만 함
                $result = $this->checkTranslatedAnswer($question, $userAnswered[0]);
            }

            Log::debug($result);
            array_push($this->answeredQuestions, $question->id);

            // Insert the current question_id, answer_id and whether it is correnct or wrong to quiz table.
            Quiz::create([
                'user_id' => auth()->id(),
                'quiz_header_id' => $this->quizid->id,
                'section_id' => $question->section_id,
                'question_id' => $question->id,
                'answer_id' => $result['answerId'],
                'user_answer' => $result['userAnswered'],
                'is_correct' => $result['isChoiceCorrect']
            ]);
        }

        $this->quizid->questions_taken = serialize($this->answeredQuestions);
        // Save the record
        $this->quizid->save();

        // 다중 선택 처리를 위해 아래는 일단 주석
        // 사용자 입력 답안지 리셋
        $this->reset('omrAnswered');
        $this->showResults();
    }

    public function checkShortAnswer($question, $userAnswered)
    {
        $answerId = $question->answers[0]->id;
        // 1. 문장내 공백은 한개씩만 유지
        $targetAnswered = trim(preg_replace("/\s+/", " ", strtolower($userAnswered)));
        $correctAnswer = trim(preg_replace("/\s+/", " ", strtolower($question->answers[0]->answer)));
        // 2. 구분자를 중심으로 단어 분리
        // // $arrayUserAnswer = preg_split("/[,:.\/\s]/", strtolower($userAnswered));
        // $arrayCorrectAnswer = preg_split("/[,:.\/\s]/", strtolower($question->answers[0]->answer));
        // 3. 두배열 차이 비교
        // $answer_diff = in_array($userAnswered, $arrayCorrectAnswer); //($arrayCorrectAnswer == $arrayUserAnswer); // array_diff($arrayCorrentAnswer, $arrayUserAnswer);
        $isChoiceCorrect = ($correctAnswer == $targetAnswered) ? '1':'0';

        return [
            'answerId' => $answerId,
            'userAnswered' => $userAnswered,
            'isChoiceCorrect' => $isChoiceCorrect
        ];
    }

    public function startEnglishCompositionClick()
    {
        Log::debug(__METHOD__);
        $this->reset('omrAnswered');
        $this->setupQuiz = false;
        $this->quizInProgress = true;

        $this->quizSize = Question::query()->where('section_id', $this->sectionId)->where('is_active', '1')->count();
        Log::debug("startQuiz quizSize=" . $this->quizSize);
        $this->quizid = QuizHeader::create([
            'user_id' => auth()->id(),
            'quiz_size' => $this->quizSize,
            'section_id' => $this->sectionId,
        ]);
        $this->count = 1;
        $this->retryCount = 0;
        $this->showRetry = false;
        $this->extraInfo = [];
        // 구문 선택 순서
        $this->selectedOrder = 0;
        // Get the first/next question for the quiz.
        // Since we are using LiveWire component for quiz, the first quesiton and answers will be displayed through mount function.
        $this->currentQuestion = $this->getNextQuestion();

        // 타이머 설정
        $this->currentTimer = $this->currentQuestion->timer;
        // 구문 섞기
        // $this->currentExample = explode('/', $this->currentQuestion->answers[0]->answer);
        // shuffle($this->currentExample);
        $this->makeShuffledExample();
    }

    public function checkSentenceOrder($exampleIndex, $value)
    {
        Log::debug(__METHOD__);
        Log::debug($value);

        $correctCount = 0;
        $userAnsweredCount = count($this->userAnswered);
        $currentExampleCount = count($this->currentExample);

        if ($this->isTimeout) {
            Log::debug(__METHOD__." timeout");
            $correctAnswers = explode('/', $this->currentQuestion->answers[0]->answer);

            foreach ($correctAnswers as $index => $answer) {
                // 틀린 경우 체크
                if (trim($this->userAnswered[$index][0]) != trim($answer)) {
                    $this->userAnswered[$index][1] = false;
                } else $correctCount++;
            }
        } else {
            // 모든 선택이 완료 된 상태에서는 추가 진행 없음
            if ($userAnsweredCount >= $currentExampleCount) {
                return ;
            }
            // 선택한 문구는 invisible
            $this->currentExample[$exampleIndex][1] = false;

            // 기본 선택시 틀린 표시는 하지 않음, 마지막 구문이 선택 된 경우 전체 구문이 순서에 맞는지 검사
            $this->userAnswered[] = [$value, true, $exampleIndex];
            $this->selectedOrder++;

            $userAnsweredCount = count($this->userAnswered);

            if ($userAnsweredCount == $currentExampleCount) {
                $correctAnswers = explode('/', $this->currentQuestion->answers[0]->answer);

                foreach ($correctAnswers as $index => $answer) {
                    // 틀린 경우 체크
                    if (trim($this->userAnswered[$index][0]) != trim($answer)) {
                        $this->userAnswered[$index][1] = false;
                    } else $correctCount++;
                }
            }
        }

        // 선택한 구문 수와 정답의 구문 수가 동일 하면 다음 문제 진행 허용
        $this->isDisabled = !(
            $correctCount == $currentExampleCount
            || ($userAnsweredCount == $currentExampleCount && ($this->retryCount+1) >= $this->currentQuestion->retry)
        );
        Log::debug($this->isDisabled);
        $this->showRetry = (($this->retryCount+1) < $this->currentQuestion->retry && $this->isDisabled);
        Log::debug($this->showRetry);
        if ($userAnsweredCount == $currentExampleCount) {
            Log::debug('call timerStop event');
            $this->emit('timerStop');
        }
    }

    public function deleteSelectedSentence($index)
    {
        Log::debug(__METHOD__);
        Log::debug($index);
        Log::debug($this->showRetry);
        // 재시도 버튼이 표시 되면 더이상 수정 불가
        if (count($this->userAnswered) < count($this->currentExample)) {
            $exampleIndex = $this->userAnswered[$index][2];
            $this->currentExample[$exampleIndex][1] = true;

            array_splice($this->userAnswered, $index, 1);
            $this->selectedOrder--;
        }
    }

    public function rearrangeUserAnswer($newIndexes)
    {
        Log::debug(__METHOD__);
        $newUserAnswered = [];
        foreach ($newIndexes as $item) {
//            Log::debug($item["value"]);
            $newUserAnswered[] = $this->userAnswered[intval($item["value"])];
        }
//        Log::debug(json_encode($this->userAnswered));
//        Log::debug(json_encode($newUserAnswered));
        unset($this->userAnswered);
        $this->userAnswered = $newUserAnswered;
    }

    /**
     * 문제 반복 테스트
     * @return void
     */
    public function retryQuestion()
    {
        Log::debug(__METHOD__);
        $this->retryCount++;
        $this->showRetry = false;
        $this->isTimeout = false;
        // 구문 선택 순서
        $this->selectedOrder = 0;
        // 구문 섞기
        $this->makeShuffledExample();

//        $userAnswered = array_reduce($this->userAnswered, function($result, $value) {
//            return (is_null($result) ? $value[0] : $result." / ".$value[0]);
//        }, null);
        $this->extraInfo[] = $this->userAnswered;
        $this->userAnswered = [];

        Log::debug("call timerRestart event");
        $this->emit('timerRestart');
        Log::debug(json_encode($this->extraInfo));
    }

    public function makeShuffledExample()
    {
        // $this->currentExample = explode('/', $this->currentQuestion->answers[0]->answer);
        // shuffle($this->currentExample);

        $this->currentExample = [];
        $currentExamples = explode('/', $this->currentQuestion->answers[0]->answer);
        foreach ($currentExamples as $index => $sentence) {
            $this->currentExample[] = [$sentence, true]; // [sentence, visible]
        }
        shuffle($this->currentExample);
        Log::debug($this->currentExample);
    }

    private function getListeningFile()
    {
        Log::debug(__METHOD__);
        return SectionFiles::where('section_id', $this->sectionId)->get();
    }
}
