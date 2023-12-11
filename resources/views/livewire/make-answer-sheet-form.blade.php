<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white mt-4 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto bg-green-200">
                <h2 class="text-2xl font-bold card bg-green-600 p-4 text-gray-100 rounded-t-lg mx-auto">문제지 생성</h2>
                <div class="max-w-auto mx-auto card p-4 bg-white rounded-b-lg shadow-md">
                    <div class="grid grid-cols-1 gap-6">
                        @if(!$flagExternalAnswerInputs)
                        <form wire:submit.prevent>
                            <label class="inline-flex items-center">
                                <span class="text-gray-700">전체 문제 수</span>
                                <input name="total_questions" type="text" wire:model.defer="total_questions" class="mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                                <button wire:click="changeSheets" id="changeSheets"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" > 문제지 생성 </button>
{{--                                <button wire:click="showExternAnswerInputs" id="showExternAnswerInputs"--}}
{{--                                        class="m-4 inline-flex items-center px-4 py-2 bg-yellow-300 hover:bg-yellow-500 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-sm text-black uppercase " > 외부 답지 입력 </button>--}}
                            </label>
                            <label class="inline-flex items-center">
                                <span class="text-gray-700">문제당 반복 테스트</span>
                                <input name="retry_answer" type="text" size="3" wire:model="retry_answer" class="mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                                <span class="text-gray-700">회</span>
                            </label>
                        </form>
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
                        <table class="min-w-full border border-slate-400">
                            <tr>
                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4" style="width: 150px;">번호</th>
                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4">문 제</th>
{{--                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4">답안(단락 구분은 '/' 문자)</th>--}}
                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4" style="width: 100px;">&nbsp;</th>
                            </tr>
                            @foreach($questions as $index => $question)
                            <tr>
                                <td rowspan="2" class="border border-slate-400 text-center bg-green-100"><span class="mx-auto">{{($index+1)}}번 문제</span> </td>
                                <td class="flex align-middle border border-slate-400 justify-items-start">
                                    지문 :<textarea rows="2" type="text" wire:model="questions.{{$index}}.title"
                                              name="questions.{{$index}}.title" class="w-11/12 mx-3">{{old($question["title"])}}</textarea>
                                </td>
                                <td rowspan="2" class="justify-items-start border border-slate-400">
                                    <button wire:click="deleteQuestion({{$index}})" class="m-4 inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" > 삭 제 </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="flex align-middle border border-slate-400 justify-items-start text-center">
                                    정답 :<textarea rows="2" type="text" wire:model="questions.{{$index}}.answer.0.answer" answer_id="{{$question["answer"][0]['answer_id']}}"
                                               placeholder="I / am / a boy."
                                                  name="questions.{{$index}}.answer.0"  class="w-11/12 mx-3">{{old($question["answer"][0]['answer'])}}</textarea>
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
