<x-app-layout>
    <x-slot name="header">
        <div class="md:flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('채점 하기') }}
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
                    [ {{ $section->name }} ] 시험 
                </h2>
                <!-- <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="tracking-wide font-bold rounded border-2 bg-green-500 text-white  transition shadow-md py-2 px-6 items-center">
                                        <tr>
                                            <th scope=" col" class="px-6 py-3 text-left text-xs font-extrabold  uppercase tracking-wider">
                                                Item
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold  uppercase tracking-wider">
                                                Details
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Name
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $section->name }}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 ">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Description
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $section->description}}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 ">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Status
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 ">
                                                <div class="text-sm text-gray-900">{{ $section->is_active === '1'  ? 'Active' : 'Not Active' }}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Details
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 ">
                                                <div class="text-sm text-gray-900">{{ $section->details }}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 ">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            Created By
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $section->user->name}}</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!-- ---------------- END NEW TABLE --------------------- -->

                <!-- --------------------- START NEW TABLE --------------------->
                @if($quiz_headers->isEmpty())
                <div class="px-4 py-5 my-3 sm:px-6">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        문제 풀이를 한 학생이 없습니다. 
                    </h1>
                </div>
                @else
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="tracking-wide font-bold rounded border-2 bg-green-500 text-white  transition shadow-md py-2 px-6 items-center">
                                        <tr>
                                            <th scope="col" class="px-8 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                                테스트 문제 수
                                            </th>
                                            <th scope="col" class="px-2 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                                테스트 날짜
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="capitalize bg-white divide-y divide-gray-200">
                                        @foreach($quiz_headers as $quiz)
                                        @php ($user = $quiz->user)
                                        <tr class="hover:bg-green-100">
                                            <td class="px-8  py-1">
                                                <div class="flex text-left">
                                                    <div class="">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <a class="text-blue-400 hover:underline" href="{{ route('scoreQuestion', ['section'=>$section->id, 'quiz_header'=>$quiz->id]) }}">
                                                                {{ $user->name }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-1">
                                                <div class="flex items-center">
                                                    <div class="">
                                                        <div class="text-sm text-gray-900">{{ $quiz->completed }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-2 py-1">
                                                <div class="text-sm text-gray-900">{{$quiz->created_at}}</div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <!-- ---------------- END NEW TABLE --------------------- -->
                            </div>
                            {{ $quiz_headers->links() }}
                        </div>
                    </div>
                </div>
                @endif
                <!-- ---------------- END NEW TABLE --------------------- -->
            </div>
        </div>
</x-app-layout>