<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            [{{$section->class_room->name}}] {{$section->name}} {{ __('문제지 만들기') }}
        </h2>
</x-slot>
<div class="max-w-7xl m-1 mx-auto sm:px-6 lg:px-8">
<!-- Pass the variables and load Livewire component -->
<livewire:make-answer-sheet-form :section="$section" :question_types="$question_types"/>
</div>
</x-app-layout>
