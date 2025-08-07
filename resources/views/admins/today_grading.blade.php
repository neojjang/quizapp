<x-app-layout>
    <x-slot name="header">
        <div class="md:flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                [{{$date}}] {{ __('시험 리스트') }}
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
                <!-- --------------------- START NEW TABLE --------------------->
{{--                <h2 class="font-semibold text-xl text-gray-800 leading-tight py-2 px-2">--}}
{{--                    [{{$date}}]--}}
{{--                </h2>--}}

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
                                            @if($student->reviewed == true)
                                                <span class="inline-flex items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-xs font-semibold text-white" style="margin-left:0px;">
{{--                                                    <p>검토 완료</p>--}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.144 1.052l-8 10a.75.75 0 01-1.121.082l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.894 7.434-9.293a.75.75 0 011.052-.144z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Right side content (date and time) --}}
                                    <div class="text-right">
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
