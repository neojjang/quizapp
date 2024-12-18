<div class="bg-white rounded-lg shadow-lg p-5 md:p-8 mx-2">

    <!-- Start of quiz box -->
{{--    @if($quizInProgress && $isOMR)--}}
    @if($quizInProgress)
        @if(in_array($sectionTypeId, [\App\Constants\Section::OMR, \App\Constants\Section::LISTENING_TEST]))
            <div class="px-4 -py-3 sm:px-6 ">
                <div class="flex max-w-auto mb-3">
                    <h1 class="text-2xl font-bold font-medium text-gray-900">[{{$classRoomName}} - {{$sectionName}}]</h1>
                </div>
                <div class="flex max-w-auto justify-between">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        <span class="text-gray-400 font-extrabold p-1">User</span>
                        <span class="font-bold p-2 leading-loose bg-blue-500 text-white rounded-lg">{{Auth::user()->name}}</span>
                    </h1>
                </div>
            </div>
            @if($sectionTypeId == \App\Constants\Section::LISTENING_TEST)

            @endif
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-4">
                <h4 class="text-xl font-bold card bg-green-600 p-4 text-gray-100 rounded-t-lg mx-auto">
                    @if($sectionTypeId == \App\Constants\Section::LISTENING_TEST)
                    다음 듣기 파일을 잘 듣고 각 문항의 답을 입력해 주세요.
                    @else
                    각 문항의 답을 입력해 주세요.
                    @endif
                </h4>
                <form wire:submit.prevent>
                    <table class="table-fixed border border-slate-400 w-full">
                        @if($sectionTypeId == \App\Constants\Section::LISTENING_TEST)
                        <tr>
                            <th colspan="2" class="border border-slate-400 bg-blue-100 border px-8 py-4">
                                @if(!is_null($mp3File) && $mp3File->count() > 0)
                                    <audio id="player" controls>
                                        <source src="{{env('AWS_URL'). $mp3File[0]->file_url }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                @endif

                            </th>
                        </tr>
                        @endif
                        <tr>
                            <th class="border border-slate-400 bg-blue-100 border px-8 py-4 w-1/6">문번</th>
                            <th class="border border-slate-400 bg-blue-100 border px-8 py-4 w-1/2">답안</th>
                        </tr>
                        @foreach($questions as $key => $question)
                            <tr>
                                <td class="border border-slate-400 text-center py-2 @if($question["question_type"] == \App\Constants\Question::SELECTIVE){{'bg-indigo-50'}}@else{{'bg-green-100'}}@endif"><span class="mx-auto">{{$question["question"]}}</span> </td>
                                <td class="border border-slate-400 justify-items-start py-2">
                                    <span class="px-5">
                                    @foreach($question->answers as $index => $answer)
                                        @if(($question->type_id-1) == \App\Constants\Question::SELECTIVE)
                                            <input type="checkbox" value="{{$answer->id .','.$answer->is_checked}}" wire:model="omrAnswered.{{$key}}.{{$index}}"
                                                   id="question.{{$key}}.{{$index}}.answer.{{$answer->answer}}"
                                                   name="question.{{$key}}.{{$index}}.answer" class="checked:bg-blue-500">
                                            <label for="question.{{$key}}.{{$index}}.answer.{{$answer->answer}}" class="mr-2">{{$answer->answer}}</label>
                                        @else
                                            <input type="text" wire:model="omrAnswered.{{$key}}.{{$index}}"
                                                   name="question.{{$key}}.{{$index}}.answer" value="{{ old('omrAnswered.'.$key) }}" class="w-11/12" style="padding-top:0px;padding-bottom:0px;"/>
                                        @endif
                                    @endforeach
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <div class="flex items-center justify-end mt-4">
                        <button wire:click="checkAllAnswers" type="submit" @if($isDisabled) disabled='disabled' @endif class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            {{ __('결과 보기') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
        @if($sectionTypeId == \App\Constants\Section::NORMAL)
            <div class="px-4 -py-3 sm:px-6 ">
                <div class="flex max-w-auto mb-3">
                    <h1 class="text-2xl font-bold font-medium text-gray-900">[{{$classRoomName}} - {{$sectionName}}]</h1>
                </div>
                <div class="flex max-w-auto justify-between">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        <span class="text-gray-400 font-extrabold p-1">User</span>
                        <span class="font-bold p-2 leading-loose bg-blue-500 text-white rounded-lg">{{Auth::user()->name}}</span>
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        <span class="text-gray-400 font-extrabold p-1">Quiz Progress </span>
                        <span class="font-bold p-3 leading-loose bg-blue-500 text-white rounded-full">{{$count .'/'. $quizSize}}</span>
                    </p>
                </div>
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                <form wire:submit.prevent>
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 mb-2 font-medium text-gray-900">
                            <span class="mr-2 font-extrabold"> {{$count}}</span> {!! nl2br($currentQuestion->question) !!}
                            @if($learningMode)
                                <div x-data={show:false} class="block text-xs">
                                    <div class="p-1" id="headingOne">
                                        <button @click="show=!show" class="underline text-blue-500 hover:text-blue-700 focus:outline-none text-xs px-3" type="button">
                                            Explanation
                                        </button>
                                    </div>
                                    <div x-show="show" class="block p-2 bg-green-100 text-xs">
                                        {{$currentQuestion->explanation}}
                                    </div>
                                </div>
                            @endif
                        </h3>
                        @foreach($currentQuestion->answers as $key => $answer)
                            <label for="question-{{$answer->id}}">
                                <div class="max-w-auto px-3 py-3 m-3 text-gray-800 rounded-lg border-2 border-gray-300 text-sm ">
                                    @if($currentQuestion->type_id != 1 && $answer->is_checked==1)
                                        <div x-show="show" class="block p-2 bg-green-100 text-xs">
                                            정확한 의미(번역/영작)를 입력하세요.
                                        </div>
                                        <textarea id="question-{{$answer->id}}" type="text" wire:model="userAnswered"
                                                  class="mt-1 bg-gray-200 block w-full text-xs  bg-graygray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" rows="2">{{ old('userAnswered') }}</textarea>
                                    @else
                                        <span class="mr-2 font-extrabold"><input id="question-{{$answer->id}}" value="{{$answer->id .','.$answer->is_checked}}" wire:model="userAnswered" type="checkbox"> </span> {{$answer->answer}}
                                    @endif
                                </div>
                            </label>
                            @if($currentQuestion->type_id != 1)
                                @break
                            @endif
                        @endforeach
                    </div>
                    <div class="flex items-center justify-end mt-4">
                        @if($count < $quizSize)
                            <button wire:click="nextQuestion" type="submit" @if($isDisabled) disabled='disabled' @endif class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                {{ __('다음 문제') }}
                            </button>
                        @else
                            <button wire:click="nextQuestion" type="submit" @if($isDisabled) disabled='disabled' @endif class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                {{ __('결과 보기') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endif
        @if($sectionTypeId == \App\Constants\Section::ENGLISH_COMPOSITION_CLICK)
            <div class="px-4 -py-3 sm:px-6 ">
                <div class="flex max-w-auto mb-3">
                    <h1 class="text-2xl font-bold font-medium text-gray-900">[{{$classRoomName}} - {{$sectionName}}]</h1>
                </div>
                <div class="flex max-w-auto justify-between">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        <span class="text-gray-400 font-extrabold p-1">User</span>
                        <span class="font-bold p-2 leading-loose bg-blue-500 text-white rounded-lg">{{Auth::user()->name}}</span>
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        <span class="text-gray-400 font-extrabold p-1">Quiz Progress </span>
                        <span class="font-bold p-3 leading-loose bg-blue-500 text-white rounded-full">{{$count .'/'. $quizSize}}</span>
                    </p>
                </div>
            </div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                <form wire:submit.prevent>
                    <div class="px-4 py-4 sm:px-6">
                        <h3 class="text-lg leading-6 mb-2 font-medium text-gray-900">
                            <span class="mr-2 font-extrabold"> {{(isset($currentQuestion->question_no) && $currentQuestion->question_no !='') ? $currentQuestion->question_no : $count}}.</span> {!! nl2br($currentQuestion->question) !!}
                        </h3>
                        @php
                        $answer_id = $currentQuestion->answers[0]->id;
                        @endphp
                        <div class="block p-2 bg-blue-50 text-sm font-bold">
                            다음 보기에서 구문을 선택하여 문장을 완성 해주세요.
                        </div>
                        <div class="block p-2 bg-green-100">
                            @foreach($currentExample as $exampleIndex => $item)
                                @if($item[1])
                                <button id="example-{{$answer_id}}-{{$exampleIndex}}"
                                        wire:click="checkSentenceOrder({{$exampleIndex}}, '{{addslashes(trim($item[0]))}}')"
                                    class="min-w-fit px-4 mb-2 rounded border-2 border-gray-800 text-base font-medium leading-normal text-primary-700  transition duration-150 ease-in-out hover:border-primary-accent-100 hover:bg-neutral-500 hover:bg-opacity-10 focus:border-primary-accent-100 focus:outline-none focus:ring-0 active:border-primary-accent-200 dark:text-primary-100 dark:hover:bg-neutral-100 dark:hover:bg-opacity-10">{{trim($item[0])}}</button>
                                @else
                                    <button id="example-{{$answer_id}}-{{$exampleIndex}}"
                                            disabled="disabled"
                                        class="min-w-fit px-4 mb-2 rounded border-2 border-gray-800 text-base font-medium leading-normal text-primary-700  transition duration-150 ease-in-out bg-gray-500 focus:border-primary-accent-100 focus:outline-none focus:ring-0 active:border-primary-accent-200 dark:text-primary-100 dark:hover:bg-neutral-100 dark:hover:bg-opacity-10">{{trim($item[0])}}</button>
                                @endif
                            @endforeach
                        </div>
                        <div class="block p-2 font-bold" >
                            결과 : <br />
                            <span class="text-red-400 text-sm">! "재시도 버튼" 표시 전까지 구문을 눌러 삭제(더블클릭) 가능합니다. <br />! 구문을 움직여 순서를 변경 할 수 있습니다.</span>
                        </div>
                        <div class="block p-2 bg-indigo-50" >
                            <ul @if(count($userAnswered) !== count($currentExample)) wire:sortable="rearrangeUserAnswer" @endif>
                            @foreach($userAnswered as $user_answer_index => $item)
                                <li id="answer-{{$answer_id}}-{{$user_answer_index}}"
                                    @if(count($userAnswered) !== count($currentExample))
                                        wire:sortable.item="{{$user_answer_index}}"
                                        wire:key="answer-{{$answer_id}}-{{$user_answer_index}}"
                                        x-on:dblclick="$wire.deleteSelectedSentence({{$user_answer_index}})"
                                    @endif
                                        @if($item[1])
                                        class="inline-flex min-w-fit px-6 py-2 mb-2 rounded border-2 border-primary-100 bg-blue-500 text-white text-sm font-medium leading-normal transition duration-150 ease-in-out hover:border-primary-accent-100 hover:bg-neutral-500 hover:bg-opacity-10 focus:border-primary-accent-100 focus:outline-none focus:ring-0 active:border-primary-accent-200 dark:text-primary-100 dark:hover:bg-neutral-100 dark:hover:bg-opacity-10"
                                        @else
                                        class="inline-flex min-w-fit px-6 py-2 mb-2 rounded border-2 border-primary-100 bg-red-400 text-sm font-medium leading-normal text-primary-700  transition duration-150 ease-in-out hover:border-primary-accent-100 hover:bg-neutral-500 hover:bg-opacity-10 focus:border-primary-accent-100 focus:outline-none focus:ring-0 active:border-primary-accent-200 dark:text-primary-100 dark:hover:bg-neutral-100 dark:hover:bg-opacity-10"
                                        @endif
                                ><span wire:sortable.handle>{{trim($item[0])}}</span></li>
                            @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-2">
                        @if ($currentQuestion->timer > 0)
                        <p class="items-center mt-1 max-w-2xl text-sm text-gray-500 px-4 py-4" >
                            <span class="text-gray-400 font-extrabold p-1">!남은 시간: </span>
                            <input type="hidden" id="current-timer" value="{{$currentTimer}}" />
                            <span id="timerContainer" class="font-bold p-3 leading-loose bg-blue-500 text-white rounded-full"><span id="timer">00:00</span> 초</span>
{{--                            <span x-show="timeLeft > 10" class="font-bold p-3 leading-loose bg-blue-500 text-white rounded-full"><span x-text="formattedTime"></span> 초</span>--}}
{{--                            <span x-show="timeLeft <= 10" class="font-bold p-3 leading-loose bg-red-500 text-white rounded-full"><span x-text="formattedTime"></span> 초</span>--}}
                        </p>
                        @endif
                        <input type="hidden" id="currentRetryCount" value="{{$retryCount}}" />
                        <input type="hidden" id="totalRetryCount" value="{{$currentQuestion->retry}}" />
                        @if($currentQuestion->retry > 0)
                        <p class="items-center mt-1 max-w-2xl text-sm text-gray-500 px-4 py-4">
                            <span class="text-gray-400 font-extrabold p-1">테스트</span>
                            <span class="font-bold p-3 leading-loose bg-blue-500 text-white rounded-full">{{($retryCount+1) .'/'. $currentQuestion->retry}} 회</span>
                        </p>
                        @endif
                        @if(count($userAnswered) == count($currentExample) || $isTimeout)
                            @if($showRetry && ($retryCount+1) < $currentQuestion->retry)
                                <button wire:click="retryQuestion" type="submit" class="m-4 inline-flex items-center px-4 py-2 bg-yellow-400 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('재시도') }}
                                </button>
                            @endif
                            @if($count < $quizSize && (($retryCount+1) >= $currentQuestion->retry || !$isDisabled))
                                <button wire:click="nextQuestion" type="submit" @if($isDisabled) disabled='disabled' @endif class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('다음 문제') }}
                                </button>
                            @endif
                            @if($count == $quizSize && (($retryCount+1) == $currentQuestion->retry || !$isDisabled))
                                <button wire:click="nextQuestion" type="submit" @if($isDisabled) disabled='disabled' @endif class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    {{ __('결과 보기') }}
                                </button>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
            <script>
                // document.addEventListener('alpine:init', () => {
                //    Alpine.data('timer', () => ());
                // });
                /*
                 x-data="{
                       init() {
                            console.log('init()');
                            window.testCall();
                       }
                   }"
                 */
                window.timerCall = function() {
                    console.log('timerCall ~~~~~');
                    //startTimer();

                    @this.on('timerRestart', () => {
                        console.log('event timerRestart')
                        startTimer();
                    });

                    @this.on('timerStop', () => {
                        console.log('event timerStop : call handleTimerEnd');
                        countDownTime();
                        handleTimerEnd();
                    });
                }
                let timerInterval = null;
                let timeLeft = 0;
                let isTimerRunning = true;
                let formattedTime = '00:00';
                const timerContainer = document.getElementById('timerContainer');
                let currentRetryCount = 0;
                let totalRetryCount = 0;

                function startTimer() {
                    console.log('startTimer');
                    timeLeft = document.getElementById('current-timer').value;
                    currentRetryCount = parseInt(document.getElementById('currentRetryCount').value);
                    totalRetryCount = parseInt(document.getElementById('totalRetryCount').value);

                    if (timerInterval) {
                        console.log('startTimer. clearInterval');
                        clearInterval(timerInterval);
                    }

                    isTimerRunning = true;

                    updateFormattedTime();
                    console.log(`startTimer this.timeLeft=${timeLeft}`);
                    if (timeLeft > 0) {
                        timerInterval = setInterval(() => {
                            if (timeLeft > 0) {
                                countDownTime();
                            } else {
                                handleTimerEnd();
                            }
                        }, 1000);
                    }
                }

                function countDownTime() {
                    timeLeft --;
                    if (timeLeft < 10) {
                        timerContainer.classList.replace('bg-blue-500', 'bg-red-500');
                    } else {
                        timerContainer.classList.replace('bg-red-500', 'bg-blue-500');
                    }
                    document.getElementById('current-timer').value = timeLeft;
                    updateFormattedTime();
                    if (timeLeft == 0) {
                        @this.isTimeout = true;
                        if (currentRetryCount+1 < totalRetryCount) {
                            @this.showRetry = true;
                            @this.isDisabled = true;
                        } else {
                            @this.showRetry = false;
                            @this.isDisabled = false;
                        }
                        console.log('countDownTime : call handleTimerEnd');
                        handleTimerEnd();
                    }
                }

                function updateFormattedTime() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;

                    formattedTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2,'0')}`;
                    document.getElementById('timer').textContent = formattedTime;
                }

                function handleTimerEnd() {
                    console.log(`handleTimerEnd`);
                    updateFormattedTime();
                    if (timerInterval) clearInterval(timerInterval);
                    timerInterval = null;
                    isTimerRunning = false;
                }
                async function handleRestart() {
                    @this.call('retryQuestion');
                    // await $wire.retryQuestion();
                    console.log('handleRestart after call to retryQuestion')
                    startTimer();
                }

                window.timerCall();
            </script>
        @endif
    @endif
    <!-- end of quiz box -->

    @if($showResult)
    <section class="text-gray-600 body-font">
        <div class="bg-white border-2 border-gray-300 shadow overflow-hidden sm:rounded-lg">
            <div class="container px-5 py-5 mx-auto">
                <div class="text-center mb-5 justify-center">
                    <h1 class=" sm:text-3xl text-2xl font-medium text-center title-font text-gray-900 mb-4">Quiz Result</h1>
                    <p class="text-md mt-10"> Dear <span class="font-extrabold text-blue-600 mr-2"> {{Auth::user()->name.'!'}} </span> You have secured <a class="bg-green-300 px-2 mx-2 hover:green-400 rounded-lg underline" href="{{route('userQuizDetails',$quizid) }}">Show quiz details</a></p>
                    <progress class="text-base leading-relaxed xl:w-2/4 lg:w-3/4 mx-auto" id="quiz-{{$quizid}}" value="{{$quizPecentage}}" max="100"> {{$quizPecentage}} </progress> <span> {{$quizPecentage}}% </span>
                </div>
                <div class="flex flex-wrap lg:w-4/5 sm:mx-auto sm:mb-2 -mx-2">
                    <div class="p-2 sm:w-1/2 w-full">
                        <div class="bg-gray-100 rounded flex p-4 h-full items-center">
                            <svg fill=" none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" class="text-indigo-500 w-6 h-6 flex-shrink-0 mr-4" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                <path d="M22 4L12 14.01l-3-3"></path>
                            </svg>
                            <span class="title-font font-medium mr-5 text-purple-700">Correct Answers</span><span class="title-font font-medium">{{$currectQuizAnswers}}</span>
                        </div>
                    </div>
                    <div class="p-2 sm:w-1/2 w-full">
                        <div class="bg-gray-100 rounded flex p-4 h-full items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" class="text-indigo-500 w-6 h-6 flex-shrink-0 mr-4" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                <path d="M22 4L12 14.01l-3-3"></path>
                            </svg>
                            <span class="title-font font-medium mr-5 text-purple-700">Total Questions</span><span class="title-font font-medium">{{$totalQuizQuestions}}</span>
                        </div>
                    </div>
                    <div class="p-2 sm:w-1/2 w-full">
                        <div class="bg-gray-100 rounded flex p-4 h-full items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" class="text-indigo-500 w-6 h-6 flex-shrink-0 mr-4" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                <path d="M22 4L12 14.01l-3-3"></path>
                            </svg>
                            <span class="title-font font-medium mr-5 text-purple-700">Percentage Scored</span><span class="title-font font-medium">{{$quizPecentage.'%'}}</span>
                        </div>
                    </div>
                    <div class="p-2 sm:w-1/2 w-full">
                        <div class="bg-gray-100 rounded flex p-4 h-full items-center">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" class="text-indigo-500 w-6 h-6 flex-shrink-0 mr-4" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                <path d="M22 4L12 14.01l-3-3"></path>
                            </svg>
                            <span class="title-font font-medium mr-5 text-purple-700">Quiz Status</span><span class="title-font font-medium">{{ $quizPecentage > 70 ? 'Pass' : 'Fail' }}</span>
                        </div>
                    </div>
                </div>
                <div class="mx-auto min-w-full p-2 md:flex m-2 justify-between">
                    <a href="{{route('userQuizDetails',$quizid) }}" class="text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">{{__('상세 보기')}}</a>
                    <a href="{{route('userQuizHome')}}" class="text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">{{__('참여한 테스트 히스토리')}}</a>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if($setupQuiz)
    <section class="text-gray-600 mx-auto body-font">
        <div class="container px-5 py-2 mx-auto">
            <div class="flex flex-wrap -m-4">
                <div class="p-4 md:w-1/2 w-full">
                    <div class="h-full bg-gray-100 p-8 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="block w-5 h-5 text-gray-400 mb-4" viewBox="0 0 975.036 975.036">
                            <path d="M925.036 57.197h-304c-27.6 0-50 22.4-50 50v304c0 27.601 22.4 50 50 50h145.5c-1.9 79.601-20.4 143.3-55.4 191.2-27.6 37.8-69.399 69.1-125.3 93.8-25.7 11.3-36.8 41.7-24.8 67.101l36 76c11.6 24.399 40.3 35.1 65.1 24.399 66.2-28.6 122.101-64.8 167.7-108.8 55.601-53.7 93.7-114.3 114.3-181.9 20.601-67.6 30.9-159.8 30.9-276.8v-239c0-27.599-22.401-50-50-50zM106.036 913.497c65.4-28.5 121-64.699 166.9-108.6 56.1-53.7 94.4-114.1 115-181.2 20.6-67.1 30.899-159.6 30.899-277.5v-239c0-27.6-22.399-50-50-50h-304c-27.6 0-50 22.4-50 50v304c0 27.601 22.4 50 50 50h145.5c-1.9 79.601-20.4 143.3-55.4 191.2-27.6 37.8-69.4 69.1-125.3 93.8-25.7 11.3-36.8 41.7-24.8 67.101l35.9 75.8c11.601 24.399 40.501 35.2 65.301 24.399z"></path>
                        </svg>
                        <p class="leading-relaxed mb-6">{{$quote->quote}}</p>
                        <a class="inline-flex items-center">
                            <span class="flex-grow flex flex-col">
                                <span class="title-font font-medium text-gray-900">Author</span>
                                <span class="inline-block h-1 w-18 rounded bg-indigo-500 mt-2 mb-1"></span>
                                <span class="text-gray-500 text-sm">{{$quote->author}}</span>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="p-4 md:w-1/2 w-full">
                    <form wire:submit.prevent="startQuiz">
                        @csrf
                        <h2 class="text-gray-900 text-lg font-medium title-font mb-5">시험 선택</h2>
                        <div class="relative mx-full mb-4 ">
                            @if($classRoomId === 0)
                            <select name="classRoom" id="classRoom_id" wire:model="classRoomId" class="block w-full mt-1 rounded-md bg-gray-100 border-2 border-gray-500 focus:bg-white focus:ring-0">
                                @if($classRooms->isEmpty())
                                <option value="">선택 가능한 수업이 없습니다.</option>
                                @else
                                <option value="">수업을 선택해 주세요.</option>
                                @foreach($classRooms as $classRoom)
                                <option value="{{$classRoom->id}}">{{$classRoom->name}}</option>
                                @endforeach
                                @endif
                            </select>
                            @else
                                <span class="text-blue-800 font-bold text-xl" >{{$classRoomName}}</span>
                            @endif
                            @error('sectionId') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="relative mx-full mb-4">
                            <select name="section" id="section_id" wire:model="sectionId" class="block w-full mt-1 rounded-md bg-gray-100 border-2 border-gray-500 focus:bg-white focus:ring-0">
                                @if($sections->isEmpty())
                                <option value="">등록 된 시험이 없습니다.</option>
                                @else
                                <option value="">시험을 선택해 주세요.</option>
                                @foreach($sections as $section)
                                <option value="{{$section->id}}" @if($section->questions_count===0) disabled @endif>{{$section->been_taken ? '[완료]':''}}{{$section->name}}</option>
                                @endforeach
                                @endif
                            </select>
                            @error('sectionId') <span class="text-red-400 text-xs">{{ __('테스트 할 시험을 선택해 주세요.') }}</span> @enderror
                        </div>
                        <div class="flex items-start py-10">
{{--                            <div class="flex items-center h-5">--}}
{{--                                <input wire:model="learningMode" id="learningMode" name="learningMode" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">--}}
{{--                            </div>--}}
{{--                            <div class="ml-3 text-sm">--}}
{{--                                <label for="learningMode" class="font-medium text-gray-700">Learning Mode?</label>--}}
{{--                                <p class="text-gray-500">If checked, this will enable explanation tab for each question.</p>--}}
{{--                            </div>--}}
                        </div>
                        <button type="submit" @if($sections->isEmpty())disabled="true" @endif
                                class="block w-full text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">Start Quiz</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @endif
</div>
