<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('시험 페이지') }}
        </h2>
    </x-slot>
    <div class="max-w-7xl m-1 mx-auto sm:px-6 lg:px-8">
        <!-- Pass the variables and load Livewire component -->
        <!-- livewire:user-quizlv major_group="{{$majorGroup}}" medium_group="{{$mediumGroup}}" class_room="{{$classRoom}}"/> -->
        @livewire('user-quizlv', ['major_group'=>$majorGroup, 'medium_group'=>$mediumGroup, 'class_room'=>$classRoom])
    </div>
</x-app-layout>
