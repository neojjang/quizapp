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
                    <a href="{{route('detailSection', $section->id)}}" class="tracking-wide font-bold rounded border-2 border-blue-500 hover:border-blue-500 bg-blue-500 text-white hover:bg-blue-600 transition shadow-md py-2 px-6 items-center">Back</a>
                </div>
                <!-- --------------------- START NEW TABLE --------------------->
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    [ {{ $section->name }} ] 시험 : {{ $user->name }}
                </h2>

                <!-- --------------------- START NEW TABLE --------------------->
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                
                                <!-- QUIZ START -->
                                @foreach($questions as  $qkey => $question)
                                @php
                                $userAnswer = array_key_exists($question->id, $userQuiz) ? $userQuiz[$question->id]:null;
                                @endphp
                                <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                                    <div class="px-4 py-5 sm:px-6">
                                        <h3 class="text-lg leading-6 mb-2 font-medium text-gray-900">
                                            <span class="mr-2 font-extrabold"> {{$qkey + 1}}</span> {{$question->question}} 
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
                                        @if(isset($userAnswer))
                                            @foreach($question->answers as $key => $answer)
                                            @if($question->type_id==2)
                                                @if($userAnswer->is_correct==='1')
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500">
                                                [O] {{$userAnswer->user_answer}}
                                                </div>
                                                @elseif($userAnswer->is_correct==='2')
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-indigo-500 font-extrabold ">
                                                [보류] {{$userAnswer->user_answer}} 
                                                </div>
                                                @else
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-red-600 font-extrabold ">
                                                [X] {{$userAnswer->user_answer}} 
                                                </div>
                                                @endif
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500">
                                                [정답]: <span class="mr-2 font-extrabold">{{$answer->answer}}</span> 
                                                </div>
                                                @break
                                            @else
                                                @if(($userAnswer->is_correct==='1') && ($answer->is_checked ==='1'))
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500">
                                                    <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}}
                                                </div>
                                                @elseif(($userAnswer->answer_id === $answer->id) && ($answer->is_checked === '0'))
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-red-600 font-extrabold ">
                                                    <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}}
                                                </div>
                                                @elseif($answer->is_checked && $userAnswer->is_correct === '0')
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-green-500 font-extrabold ">
                                                    <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} <span class="p-1 font-extrabold">(Correct Answer)</span>
                                                </div>
                                                @else
                                                <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-gray-500 font-extrabold ">
                                                    <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}}
                                                </div>
                                                @endif
                                            @endif
                                            @endforeach
                                        @else
                                            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-black bg-red-400  ">
                                            답하지 않았습니다. 
                                            </div>
                                        @endif
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