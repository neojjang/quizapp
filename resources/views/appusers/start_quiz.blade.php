<x-app-layout>
    <x-slot name="header">
        <div class="md:flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if(isset($majorGroup) && !isset($mediumGroup))
                    <a class="text-blue-400 hover:underline" href="/quiz/start">시작</a>
                    &gt;
                    [<a class="text-blue-400 hover:underline" href="{{ route('startQuiz', ['major_group'=>$majorGroup->id]) }}">{{$majorGroup->name}}</a>]
                    &gt; {{ __('수업 중분류 선택') }}
                @endif
                @if(isset($majorGroup) && isset($mediumGroup))
                    <a class="text-blue-400 hover:underline" href="/quiz/start">시작</a>
                    &gt;
                    [<a class="text-blue-400 hover:underline" href="{{ route('startQuiz', ['major_group'=>$majorGroup->id]) }}">{{$majorGroup->name}}</a>]
                    &gt; [<a class="text-blue-400 hover:underline" href="{{ route('startQuiz', ['major_group'=>$majorGroup->id, 'medium_group'=>$mediumGroup->id]) }}">{{$mediumGroup->name}}</a>]
                    &gt; {{ __('수업 선택') }}
                @endif
                @if(!isset($majorGroup)){{ __('수업 대분류 선택') }}@endif
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto">
                @if($targetGroups->isEmpty())
                <div class="px-4 py-5 sm:px-6">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        등록 된 수업 대분류가 없습니다.
                    </h1>
                </div>
                @else
                <!-- --------------------- START NEW TABLE --------------------->
                <div class="flex flex-col">
                    {{ $targetGroups->links() }}

                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="tracking-wide font-bold rounded border-2 bg-green-500 text-white  transition shadow-md py-2 px-6 items-center">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 sm:text-left text-xl font-bold text-white uppercase tracking-wider">
                                                이름
                                            </th>
                                            <th scope="col" class="px-2 py-3 text-center text-xl font-bold text-white uppercase tracking-wider md:w-1/4">
                                                @if(!isset($majorGroup))
                                                {{__('수업 중분류 수')}}
                                                @elseif(!isset($mediumGroup))
                                                {{__('수업 수')}}
                                                @else
                                                {{__('시험 수')}}
                                                @endif
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="capitalize bg-white divide-y divide-gray-200">
                                        @foreach($targetGroups as $section)
                                        <tr class="hover:bg-green-100">
                                            <td class="px-2 text-left py-2">
                                                <div class="flex items-start">
                                                    <div class="ml-4">
                                                        <div class="text-lg font-medium text-gray-900">
                                                            <a class="text-blue-400 hover:underline"
                                                                @if(!isset($majorGroup))
                                                                href="{{ route('startQuiz', $section->id) }}">
                                                                @elseif(!isset($mediumGroup))
                                                                href="{{ route('startQuiz', ['major_group'=>$majorGroup->id, 'medium_group'=>$section->id]) }}">
                                                                @else
                                                                href="{{ route('startQuiz', ['major_group'=>$majorGroup->id, 'medium_group'=>$mediumGroup->id, 'class_room'=>$section->id]) }}">
                                                                @endif
                                                                    {{ $section->name}}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-center md:w-1/4">
                                                <div class="text-lg text-gray-900">
                                                    @if(!isset($majorGroup))
                                                    {{ $section->medium_groups_count }}
                                                    @elseif(!isset($mediumGroup))
                                                    {{ $section->class_rooms_count }}
                                                    @else
                                                        {{ $section->sections_count }}
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    {{ $targetGroups->links() }}
                </div>
                @endif
                <!-- ---------------- END NEW TABLE --------------------- -->
            </div>
        </div>
    </div>
    <!-- Modal toggle -->
    @push('js')
        <script type="text/javascript">
        </script>
    @endpush
</x-app-layout>
