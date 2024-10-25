<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quiz Deatil') }}
        </h2>
    </x-slot>
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <!-- QUIZ HEADER START -->

        <div class="bg-white border-2 border-gray-300 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h1 class="text-sm leading-6 font-medium text-gray-900">
                    Quiz Information
                </h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-700">

                    <!-- \Carbon\Carbon::isDayOff($userQuizDetails->updated_at) -->
                    You took this quiz {{$userQuizDetails->updated_at->diffForHumans()}} on <span class="text-bold bg-green-300 px-2 rounded-lg"> {{$userQuizDetails->updated_at}} </span>
                </p>
            </div>
            <div class="border-t border-gray-300">
                <dl>
                    <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-700">
                            Section Title
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{$userQuizDetails->section->name}}
                            <p class="mt-1 max-w-2xl text-sm text-gray-700">
                                {{$userQuizDetails->section->description}}
                            </p>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-700">
                            Quiz Completion Status
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{$userQuizDetails->completed ? 'Completed' : 'Not Completed'}}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-700">
                            Total Quiz Questions
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{$userQuizDetails->quiz_size}}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-700">
                            Quiz Score
                        </dt>
                        @if($userQuizDetails->score < 70) <dd class="mt-1 px-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 bg-red-300 rounded-lg">
                            {{$userQuizDetails->score .' %'}}
                            </dd>
                            @else
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 bg-green-300 rounded-lg">
                                {{$userQuizDetails->score .' %'}}
                            </dd>
                            @endif
                    </div>
                    <div class="bg-white px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-700">
                            Quiz Duration
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{$userQuizDetails->updated_at->diffInMinutes($userQuizDetails->created_at) .' Minutes'}}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- QUIZ HEADER END -->
        @foreach($quizQuestions as $key => $question)
        @php
        $userAnswer = $userQuiz[$key];
        if (in_array($question->type_id, [(\App\Constants\Question::SELECTIVE+1)])) {
            $userAnswer->user_answer = explode(",/", $userAnswer->user_answer);
        }
        @endphp
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 mb-2 font-medium text-gray-900">
                    <span class="mr-2 font-extrabold"> {{$key + 1}}</span> {{$question->question}} [{{$resultMark[$userAnswer->is_correct]}}][재시도 {{$userAnswer->retry}}회]
                    <div x-data={show:false} class="block text-xs">
                        <div class="p-1" id="headingOne">
                            <button @click="show=!show" class="underline text-blue-500 hover:text-blue-700 focus:outline-none text-xs " type="button">
                                Explanation
                            </button>
                        </div>
                        <div x-show="show" class="block p-2 bg-green-100 text-xs">
                            {{$question->explanation}}
                        </div>
                    </div>
                </h3>
                @foreach($question->answers as $key => $answer)
                    @if(in_array(($question->type_id-1),[\App\Constants\Question::TRANSLATION, \App\Constants\Question::ENGLISH_COMPOSITION, \App\Constants\Question::SHORT_ANSWER, \App\Constants\Question::ENGLISH_COMPOSITION_CLICK]))
                        @if($userAnswer->is_correct==='1')
                        <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white font-semibold  bg-blue-500">
                        [O] {{$userAnswer->user_answer}}
                        </div>
                        @elseif($userAnswer->is_correct==='2')
                        <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-indigo-500 font-bold ">
                        [보류] {{$userAnswer->user_answer}}
                        </div>
                        @else
                        <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-red-600 font-bold ">
                        [X] {{$userAnswer->user_answer}}
                        </div>
                        @endif
                        <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500">
                        [정답]: @hasrole('admin|superadmin') <span class="mr-2 font-extrabold">{{$answer->answer}}</span> @endhasrole
                        </div>
                        @break
                    @elseif(in_array(($question->type_id-1),[\App\Constants\Question::SELECTIVE]))
                        @if(($userAnswer->is_correct==='1') && ($answer->is_checked ==='1'))
                            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500 font-extrabold">
                                <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} @if(in_array($answer->id, $userAnswer->user_answer))&check;@endif
                            </div>
                        @elseif((in_array($answer->id, $userAnswer->user_answer)) && ($answer->is_checked === '0'))
                            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-red-600 font-extrabold ">
                                <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} @if(in_array($answer->id, $userAnswer->user_answer))&check;@endif
                            </div>
                        @elseif($answer->is_checked && $userAnswer->is_correct === '0')
                            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-green-500 font-extrabold ">
                                <span class="p-1 font-extrabold">[정답]:</span>
                                @hasrole('admin|superadmin')<span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} @if(in_array($answer->id, $userAnswer->user_answer))&check;@endif @endhasrole
                            </div>
                        @else
                            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-gray-500 ">
                                @hasrole('admin|superadmin')<span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}}@endhasrole
                            </div>
                        @endif
                    @else

                    @endif
                @endforeach
            </div>
        </div>
        @endforeach
        <div class="mx-auto min-w-full p-2 md:flex m-2 justify-between">
            <a href="javascript:history.back()" class="text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">{{__('Back')}}</a>
            <a href="{{route('userQuizHome')}}" class="text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">{{__('참여한 테스트 히스토리')}}</a>
        </div>
    </div>
</x-app-layout>
