{{--<div class="container h-48 overflow-y-auto p-2" wire:poll.3s="loadTodayTesters">--}}
{{--    @if(!isset($testers))--}}
{{--        <div class="bg-gray-100 p-6 rounded-lg shadow-md text-center text-gray-500">--}}
{{--            참여 학생이 아직 없습니다.--}}
{{--        </div>--}}
{{--    @else--}}
{{--        <div class="grid gap-2">--}}
{{--            @foreach($testers as $student)--}}
{{--                <div class="bg-white p-2 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">--}}
{{--                    <p class="text-gray-700 text-sm ">--}}
{{--                        <a class="text-blue-400 hover:underline" href="{{ route('scoreQuestion', ['section'=>$student->section->id, 'quiz_header'=>$student->id]) }}">{{$student->user->name}}</a>--}}
{{--                        [ <a class="text-blue-400 hover:underline" href="{{ route('detailSection', $student->section->id) }}">{{ Str::limit($student->section->name, 150) }}</a> ]--}}
{{--                        (--}}
{{--                        @if($student->created_at->diffInHours() <= 2)--}}
{{--                            {{ $student->created_at->diffForHumans() }}--}}
{{--                        @else--}}
{{--                            {{ $student->created_at->format('Y-m-d H:i') }}--}}
{{--                        @endif--}}
{{--                        )--}}
{{--                    </p>--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        </div>--}}
{{--    @endif--}}
{{--</div>--}}
{{--class=" border border-gray-400"--}}
<div class="container h-48 overflow-y-auto p-3 bg-gray-100 rounded-lg shadow-md" wire:poll.3s="loadTodayTesters">
    @if(!isset($testers) || $testers->isEmpty())
        <div class="bg-white p-3 rounded-lg shadow-md text-center text-gray-500 font-bold">
            참여 학생이 아직 없습니다.
        </div>
    @else
        <div class="space-y-2">
            @foreach($testers as $student)
                <div class="bg-white p-3 rounded-lg hover:shadow-lg transition duration-300">
                    <p class="text-gray-700 text-sm truncate">
                        <a class="font-medium text-blue-500 hover:underline font-semibold" href="{{ route('scoreQuestion', ['section'=>$student->section->id, 'quiz_header'=>$student->id]) }}">{{$student->user->name}}</a>
                        <span class="text-gray-500">in</span>
                        <a class="text-blue-500 hover:underline font-semibold" href="{{ route('detailSection', $student->section->id) }}">{{ Str::limit($student->section->name, 30) }}</a>
                        <span class="text-gray-500">
                            (
                            @if($student->created_at->diffInHours() <= 2)
                                {{ $student->created_at->diffForHumans() }}
                            @else
                                {{ $student->created_at->format('Y-m-d H:i') }}
                            @endif
                            )
                        </span>
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>
