<div>
@if(isset($userAnswer))
    @if(in_array(($question->type_id-1), [\App\Constants\Question::TRANSLATION, \App\Constants\Question::ENGLISH_COMPOSITION, \App\Constants\Question::SHORT_ANSWER, \App\Constants\Question::ENGLISH_COMPOSITION_CLICK]))
        @php($answer = $question->answers[0])
        @if(isset($userAnswer->extra_info) && $userAnswer->extra_info != "")
            @php($retry_data = json_decode($userAnswer->extra_info))
            @foreach($retry_data as $retry_index => $value)
                <div class="mt-1 max-w-auto text-sm px-2 text-black bg-gray-200">
                    [{{($retry_index+1)}}] {{$value}}
                </div>
            @endforeach
        @endif
        @if($userAnswer->is_correct==='1')
        <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500 font-extrabold">
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
        <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-black bg-none">
            <input type="checkbox" wire:click="changeUserAnswer($event.target.value)" value="1" {{$userAnswer->is_correct==='1'?'checked':''}}>정답</input>
            | <input type="checkbox" wire:click="changeUserAnswer($event.target.value)" value="2" {{$userAnswer->is_correct==='2'?'checked':''}}>보류</input>
            | <input type="checkbox" wire:click="changeUserAnswer($event.target.value)" value="0" {{$userAnswer->is_correct==='0'?'checked':''}}>오답</input>
            <!-- | <input type="checkbox" wire:click="changeUserAnswer($event.target.value)" value="3">테스트</input> -->
        </div>
    @else
        @foreach($question->answers as $key => $answer)
            @if(($userAnswer->is_correct==='1') && ($answer->is_checked ==='1'))
            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-none bg-green-500">
                <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} @if(in_array($answer->id, $userAnswer->user_answer))&check;@endif
            </div>
            @elseif((in_array($answer->id, $userAnswer->user_answer)) && ($answer->is_checked === '0'))
            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-red-600 font-extrabold ">
                <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} @if(in_array($answer->id, $userAnswer->user_answer))&check;@endif
            </div>
            @elseif($answer->is_checked && $userAnswer->is_correct === '0')
            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-white bg-green-500 font-extrabold ">
                <span class="p-1 font-extrabold">[정답]:</span>
                <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}} @if(in_array($answer->id, $userAnswer->user_answer))&check;@endif
            </div>
            @else
            <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-gray-500 ">
                <span class="mr-2 font-extrabold">{{$choice->values()->get($key)}} </span> {{$answer->answer}}
            </div>
            @endif
        @endforeach
    @endif

@else
    <div class="mt-1 max-w-auto text-sm px-2 rounded-lg text-black bg-red-400  ">
    답하지 않았습니다.
    </div>
@endif
</div>
