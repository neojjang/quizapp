<x-app-layout>
    <x-slot name="header">
        <div class="md:flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('채점') }}
            </h2>
        </div>
    </x-slot>
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto">
                <div class="flex justify-between items-center py-4">
                    <a href="{{route('scoreSection', $section->id)}}" class="tracking-wide font-bold rounded border-2 border-blue-500 hover:border-blue-500 bg-blue-500 text-white hover:bg-blue-600 transition shadow-md py-2 px-6 items-center">Back</a>
                </div>
                <!-- --------------------- START NEW TABLE --------------------->
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    [ {{ $section->name }} ] 시험 : {{ $user->name }} <span class="text-xs">( 테스트 시간 : {{$quizHeader->created_at}} )</span>
                </h2>

                <!-- --------------------- START NEW TABLE --------------------->
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">

                                <!-- QUIZ START -->
                                @foreach($questions as  $qkey => $question)
                                @php
                                $userAnswer = isset($userQuiz[$question->id]) ? $userQuiz[$question->id]:null;
                                @endphp
                                <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                                    <div class="px-4 py-5 sm:px-6">
                                        <h3 class="text-lg leading-6 mb-2 font-medium text-gray-900">
                                            <span class="mr-2 font-extrabold"> {{$qkey + 1}}</span> {!! nl2br($question->question) !!} [{{$resultMark[$userAnswer->is_correct]}}]
                                            @if(in_array(($question->type_id-1), [\App\Constants\Question::ENGLISH_COMPOSITION_CLICK]))
                                                [재시도 {{$userAnswer->retry}}회]
                                            @endif
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
                                        @livewire('user-answer', ['userAnswer' => $userAnswer, 'question' => $question, 'quizHeader' => $quizHeader], key($question->id))

                                    </div>
                                </div>
                                @endforeach
                                <!-- QUIZ END -->

                            </div>
                        </div>
                    </div>
                </div>
                <!-- ---------------- END NEW TABLE --------------------- -->
            </div>
        </div>
</x-app-layout>
