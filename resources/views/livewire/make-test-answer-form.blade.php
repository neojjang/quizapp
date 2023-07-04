<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white mt-4 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto bg-green-200">
                <h2 class="text-2xl font-bold card bg-green-600 p-4 text-gray-100 rounded-t-lg mx-auto">OMR 카드 답안 생성</h2>
                <div class="max-w-auto mx-auto card p-4 bg-white rounded-b-lg shadow-md">
                    <div class="grid grid-cols-1 gap-6">
                        @if(!$flagExternalAnswerInputs)
                        <form wire:submit.prevent>
                            <label class="flex items-center">
                                <span class="text-gray-700">전체 문제 수</span>
                                <input name="total_questions" type="text" wire:model.defer="total_questions" class="mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                                <button wire:click="changeSheets" id="changeSheets"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" > 답지 생성 </button>
                                <button wire:click="showSetQuestionStartNo(true)" id="showSetQuestionStartNo"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-sky-300 hover:bg-sky-500 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-sm text-black uppercase " > 문제시작번호 설정 </button>
                                <button wire:click="showExternAnswerInputs" id="showExternAnswerInputs"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-yellow-300 hover:bg-yellow-500 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-sm text-black uppercase " > 외부 답지 입력 </button>
                            </label>
                        </form>
                        @endif
                        @if($flagSetQuestionStartNo)
                                <div>
                                    <label class="flex items-center">
                                        <span class="text-gray-700">문제 시작 번호 </span>
                                        <input name="question_start_no" type="text" wire:model.defer="question_start_no" class="mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                                    <button wire:click="setQuestionStartNo" id="setQuestionStartNo"
                                            class="m-4 inline-flex items-center px-4 py-2 bg-blue-400 hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300 border border-transparent rounded-md font-semibold text-sm text-white uppercase " > 설 정 </button>
                                    <button wire:click="showSetQuestionStartNo(false)" id="hideExternAnswerInputs"
                                            class="m-4 inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent text-xs rounded-md  font-semibold text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25" > {{' 취   소 '}}</button>
                                    </label>

                                </div>
                        @endif
                        @if($flagExternalAnswerInputs)
                            <div>
                                <spam class="text-bold text-red-500">외부 답지를 복사해 넣고, [답지 생성] 버튼을 눌러 주세요.</spam>
                                <textarea wire:model.defer="external_answers" name="external_answer_inputs" rows="10" class="mt-1 px-2 py-2 bg-gray-200 block w-full text-sm  bg-graygray-100 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" focus></textarea>
                                <button wire:click="hideExternAnswerInputs" id="hideExternAnswerInputs"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent text-xs rounded-md  font-semibold text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25" > {{' 취   소 '}}</button>
                                <button wire:click="makeAnswerSheets" id="makeAnswerSheets"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-sky-300 hover:bg-sky-500 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-sm text-black uppercase " > 답지 생성 </button>
                            </div>
                        @endif
                        @if(!$flagExternalAnswerInputs)
                        @if(count($questions) > 0)
                        <table class="table-fixed border border-slate-400">
                            <tr>
                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4">문번
                                    <button wire:click="rearrangeQuestionNo" id="rearrange-question-no"
                                            class="inline-flex items-center px-4 py-2 bg-red-300 hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring focus:ring-red-500 border border-transparent rounded-md font-semibold text-sm text-black uppercase " > 재정렬 </button>
                                </th>
                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4">답안</th>
                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4">&nbsp;</th>
                            </tr>
                            @foreach($questions as $index => $question)
                            <tr>
                                <td class="border border-slate-400 text-center @if($question["question_type"] == \App\Constants\Question::SELECTIVE){{'bg-indigo-50'}}@else{{'bg-green-100'}}@endif"><span class="mx-auto">{{$question['title']}}</span> </td>
                                <td class="border border-slate-400 justify-items-start">
                                    @if($question["question_type"] == \App\Constants\Question::SELECTIVE)
                                        <span class="px-5">
                                    <input type="checkbox" wire:model="questions.{{$index}}.answer.0"
                                           id="questions.{{$index}}.answer.0" class="checked:bg-blue-500" >
                                    <label for="questions.{{$index}}.answer.1" class="mr-2">1</label>
                                    <input type="checkbox" wire:model="questions.{{$index}}.answer.1"
                                           id="questions.{{$index}}.answer.1" class="checked:bg-blue-500" >
                                    <label for="questions.{{$index}}.answer.1" class="mr-2">2</label>
                                    <input type="checkbox" wire:model="questions.{{$index}}.answer.2"
                                           id="questions.{{$index}}.answer.2" class="checked:bg-blue-500" >
                                    <label for="questions.{{$index}}.answer.2" class="mr-2">3</label>
                                    <input type="checkbox" wire:model="questions.{{$index}}.answer.3"
                                           id="questions.{{$index}}.answer.3" class="checked:bg-blue-500" >
                                    <label for="questions.{{$index}}.answer.3" class="mr-2">4</label>
                                    <input type="checkbox" wire:model="questions.{{$index}}.answer.4"
                                           id="questions.{{$index}}.answer.4" class="checked:bg-blue-500" >
                                    <label for="questions.{{$index}}.answer.4">5</label>
                                        </span>
                                    @else
                                        <input type="text" wire:model="questions.{{$index}}.answer.0"
                                               name="questions.{{$index}}.answer" value="{{old($question["answer"][0])}}" class="w-11/12 mx-3"/>
                                    @endif

                                </td>
                                <td class="justify-items-start border border-slate-400">
                                    @if($question["question_type"] == \App\Constants\Question::SELECTIVE)
                                    <button wire:click="changeQuestionType({{$index}}, {{\App\Constants\Question::SHORT_ANSWER}})" class="m-4 inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" > 주관식(단답형) 입력 변환 </button>
                                    @else
                                    <button wire:click="changeQuestionType({{$index}}, {{\App\Constants\Question::SELECTIVE}})" class="m-4 inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" > 객관식 입력 변환 </button>
                                    @endif
                                    <button wire:click="deleteQuestion({{$index}})" class="m-4 inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" > 삭 제 </button>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                        <form wire:submit.prevent="saveQuestions">
                            <div class="flex items-center justify-start mt-4">
                                <a href="{{route('detailSection', $section->id)}}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent text-xs rounded-md font-semibold text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">Back</a>
                                <x-jet-button type="submit" class="text-center ml-4 bg-blue-500">
                                    {{ __('  저  장  ') }}
                                </x-jet-button>
                            </div>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
