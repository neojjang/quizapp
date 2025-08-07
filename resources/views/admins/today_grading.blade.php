<x-app-layout>
    <x-slot name="header">
        <div class="md:flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('채점 하기') }}
            </h2>
        </div>
    </x-slot>
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <section class="text-gray-600 body-font">
            <div class="container px-5 py-5 mx-auto">
                @livewire('test-calendar', ['target_date' =>$date])
            </div>
        </section>
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto">
{{--                <div class="flex justify-between items-center py-4">--}}
{{--                    <a href="{{route('detailSection', $quiz_headers->section->id)}}" class="tracking-wide font-bold rounded border-2 border-blue-500 hover:border-blue-500 bg-blue-500 text-white hover:bg-blue-600 transition shadow-md py-2 px-6 items-center">Back</a>--}}
{{--                </div>--}}
                <!-- --------------------- START NEW TABLE --------------------->
                <h2 class="font-semibold text-xl text-gray-800 leading-tight py-2 px-2">
{{--                    [ {{ $quiz_headers->section->name }} ] 시험--}}
                    [{{$date}} 참여자 리스트]
                </h2>

                <!-- --------------------- START NEW TABLE --------------------->

                @if($quiz_headers->isEmpty())
                <div class="px-4 py-5 my-3 sm:px-6">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        문제 풀이를 한 학생이 없습니다.
                    </h1>
                </div>
                @else
{{--                    <div class="space-y-2">--}}
{{--                        @foreach($quiz_headers as $student)--}}
{{--                            <div class="bg-white p-3 rounded-lg hover:shadow-lg transition duration-300">--}}
{{--                                <p class="text-gray-700 text-sm truncate">--}}
{{--                                    <a class="font-medium text-blue-500 hover:underline font-semibold" href="{{ route('scoreQuestion', ['section'=>$student->section->id, 'quiz_header'=>$student->id]) }}">{{$student->user->name}}</a>--}}
{{--                                    <span class="text-gray-500">in</span>--}}
{{--                                    <a class="text-blue-500 hover:underline font-semibold" href="{{ route('detailSection', $student->section->id) }}">{{ Str::limit($student->section->name, 30) }}</a>--}}
{{--                                    <span class="text-gray-500">--}}
{{--                            (--}}
{{--                            @if($student->created_at->diffInHours() <= 2)--}}
{{--                                            {{ $student->created_at->diffForHumans() }}--}}
{{--                                        @else--}}
{{--                                            {{ $student->created_at->format('Y-m-d H:i') }}--}}
{{--                                        @endif--}}
{{--                                        --}}
{{--                        </span>--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}
{{--                    </div>--}}
                    <div class="space-y-4 py-2 px-2">
                        @foreach($quiz_headers as $student)
                            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-200">
                                <div class="p-4 flex items-center justify-between">
                                    {{-- Left side content (user name, section name) --}}
                                    <div class="flex-grow">
                                        <div class="flex items-center space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                            <a class="font-bold text-lg text-gray-800 hover:text-blue-600 transition-colors duration-200" href="{{ route('scoreQuestion', ['section'=>$student->section->id, 'quiz_header'=>$student->id]) }}">
                                                {{ Str::limit($student->user->name, 20) }}
                                            </a>
                                            <span class="text-gray-400 font-light">in</span>
                                            <a class="font-medium text-blue-500 hover:underline" href="{{ route('detailSection', $student->section->id) }}">
                                                {{ Str::limit($student->section->name, 50) }}
                                            </a>
                                            &nbsp;
                                            @if($student->reviewed == true)
                                                <span class="inline-flex items-center justify-center rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-semibold text-white">
                                                    <p>검토 완료</p>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Right side content (date and time) --}}
                                    <div class="flex-shrink-0 text-right">
                                        <span class="text-gray-500 text-sm font-medium">
                                            @if($student->created_at->diffInHours() <= 2)
                                                {{ $student->created_at->diffForHumans() }}
                                            @else
                                                {{ $student->created_at->format('Y-m-d H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <!-- ---------------- END NEW TABLE --------------------- -->
            </div>
        </div>
</x-app-layout>
