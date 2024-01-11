<x-app-layout>
    @if(isset($mediumGroup))
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                [{{$mediumGroup->name}}] - {{ __('수업 생성') }}
            </h2>
        </x-slot>
    @else
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('수업 생성') }}
        </h2>
    </x-slot>
    @endif
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto">
                <h2 class="text-2xl font-bold card bg-green-600 p-4 text-gray-100 rounded-t-lg mx-auto">신규 수업</h2>
                <div class="mt-2 max-w-auto mx-auto card p-4 bg-white rounded-b-lg shadow-md">
                    <div class="grid grid-cols-1 gap-6">
                        <form action="{{isset($mediumGroup) ? route('storeClassRoomWithMediumGroup', $mediumGroup->id):route('storeClassRoom')}}" method="post">
                            @csrf
                            @if(isset($mediumGroups))
                                <label class="block">
                                    <span class="text-gray-700">수업 대분류</span>
                                    @error('name')
                                    <span class="text-red-700 text-xs content-end float-right">{{$message}}</span>
                                    @enderror
                                    <select name="medium_group_id"  class="block w-full mt-1 rounded-md bg-gray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0">
                                        @foreach($mediumGroups as $section)
                                            <option value="{{$section->id}}">{{$section->name}}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endif
                            <label class="block mt-2">
                                <span class="text-gray-700">이름</span>
                                @error('name')
                                <span class="text-red-700 text-xs content-end float-right">{{$message}}</span>
                                @enderror
                                <input name="name" value="{{ old('name') }}" type="text" class="mt-1 block w-full rounded-md bg-gray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                            </label>
                            <label class="block mt-2">
                                <span class="text-gray-700">설명</span>
                                @error('description')
                                <span class="text-red-700 text-xs content-end float-right">{{$message}}</span>
                                @enderror
                                <input name="description" value="{{ old('description') }}" type="text" class="mt-1 block w-full rounded-md bg-gray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                            </label>
                            <label class="block mt-2">
                                <span class="text-gray-700">공개</span>
                                @error('is_active')
                                <span class="text-red-700 text-xs content-end float-right">{{$message}}</span>
                                @enderror
                                <select name="is_active" value="{{ old('is_active') }}" class="block w-full mt-1 rounded-md bg-gray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </label>
                            <label class="block mt-2">
                                <span class="text-gray-700">상세 설명</span>
                                @error('details')
                                <span class="text-red-700 text-xs content-end float-right">{{$message}}</span>
                                @enderror
                                <textarea name="details" value="{{ old('details') }}" class="mt-1 bg-gray-100 block w-full rounded-md bg-graygray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" rows="3"></textarea>
                            </label>
                            <div class="flex items-center justify-end mt-4">
                                <a href="{{route('listClassRoom')}}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">Back</a>

                                <x-jet-button type="submit" class="ml-4">
                                    {{ __(' 등 록 ') }}
                                </x-jet-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
