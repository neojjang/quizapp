<div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="flex justify-between items-center mb-4">
        <button wire:click="previousMonth" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150">&lt;</button>
{{--        <h2 class="text-xl font-bold">{{ $monthName }}</h2>--}}
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold">{{ $monthName }}</h2>
            {{-- 새로고침 아이콘 버튼 --}}
            <button wire:click="refresh" class="p-1 text-gray-500 rounded-full hover:bg-gray-200 hover:text-gray-700 transition duration-150">
                <svg fill="#000000" height="18px" width="18px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                     viewBox="0 0 512 512" xml:space="preserve">
                <g>
                    <g>
                        <path d="M511.957,185.214L512,15.045l-67.587,67.587l-7.574-7.574c-48.332-48.332-112.593-74.95-180.946-74.95
                            C114.792,0.107,0,114.901,0,256s114.792,255.893,255.893,255.893S511.785,397.099,511.785,256h-49.528
                            c0,113.79-92.575,206.365-206.365,206.365S49.528,369.79,49.528,256S142.103,49.635,255.893,49.635
                            c55.124,0,106.947,21.467,145.925,60.445l7.574,7.574l-67.58,67.58L511.957,185.214z"/>
                    </g>
                </g>
                </svg>
            </button>
        </div>

        <button wire:click="nextMonth" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150">&gt;</button>
    </div>

    <div class="grid grid-cols-7 text-center font-semibold text-gray-600 mb-2">
        <div>일</div>
        <div>월</div>
        <div>화</div>
        <div>수</div>
        <div>목</div>
        <div>금</div>
        <div>토</div>
    </div>

    <div class="grid grid-cols-7 gap-2">
        @foreach($calendarDays as $day)
            @if($day)
                @php
                    $dayKey = $day->format('Y-m-d');
                    $testerCount = $testers[$dayKey] ?? 0;
                @endphp
                @if($testerCount > 0)
                    <a href="{{route('todayGrading', $dayKey)}}" class="relative min-h-24 p-2 border rounded-md hover:bg-yellow-400  {{ ($day->isCurrentDay())? 'bg-blue-100 border-blue-400' : ($target_day == $day->day) ? 'bg-yellow-400 border-yellow-400' :'bg-gray-50 border-gray-200' }}">
                        <span class="font-bold text-gray-800">{{ $day->day }}</span>
                        <div class="mt-1 space-y-1">
                            <div class="bg-blue-500 text-white text-sm font-medium px-1 rounded truncate " title="{{$dayKey}}-{{ $testerCount }}">
                                {{ $testerCount }}명
                            </div>
                        </div>
                    </a>
                @else
                    <div class="relative min-h-24 p-2 border rounded-md {{ $day->isCurrentDay() ? 'bg-blue-100 border-blue-400' : 'bg-gray-50 border-gray-200' }}">
                        <span class="font-bold text-gray-800">{{ $day->day }}</span>
                    </div>
                @endif
{{--                <div class="relative min-h-24 p-2 border rounded-md {{ $day->isCurrentDay() ? 'bg-blue-100 border-blue-400' : 'bg-gray-50 border-gray-200' }}">--}}
{{--                    <span class="font-bold text-gray-800">{{ $day->day }}</span>--}}
{{--                    @if($testerCount > 0)--}}
{{--                        <div class="mt-1 space-y-1">--}}
{{--                            <div class="bg-blue-500 text-white text-sm font-medium px-1 rounded truncate hover:bg-yellow-600 cursor-pointer" title="{{$dayKey}}-{{ $testerCount }}">--}}
{{--                                <a href="{{route('todayGrading', $dayKey)}}">참여자 {{ $testerCount }}명</a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                </div>--}}
            @else
                <div class="min-h-24 p-2 bg-transparent rounded-md"></div>
            @endif
        @endforeach
    </div>
</div>
