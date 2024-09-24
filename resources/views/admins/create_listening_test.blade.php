<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            [{{$section->class_room->name}}] {{$section->name}} {{ __('듣기평가 답지 만들기') }}
        </h2>
    </x-slot>
    @if(session()->has('success'))
        <h2 class="text-medium font-bold rounded-xl bg-green-400 px-2 text-white">
            {{session('success')}}
        </h2>
    @elseif(session()->has('error'))
        <h2 class="text-medium font-bold rounded-xl bg-red-400 px-2 text-white">
            {{session('error')}}
        </h2>
    @endif
<div class="max-w-7xl m-1 mx-auto sm:px-6 lg:px-8">
<!-- Pass the variables and load Livewire component -->
<livewire:make-listening-test-form :section="$section" :question_types="$question_types"/>
</div>
</x-app-layout>
