<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white mt-4 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto bg-green-200">
                <h2 class="text-2xl font-bold card bg-green-600 p-4 text-gray-100 rounded-t-lg mx-auto">영작문제지 생성</h2>
                <div class="max-w-auto mx-auto card p-4 bg-white rounded-b-lg shadow-md">
                    <div class="grid grid-cols-1" x-data={showUploadFile:false} >
{{--                        <form wire:submit.prevent>--}}
                        <div class="inline-flex items-center" x-show="!showUploadFile">
                            <span class="text-gray-700">전체 문제 수</span>
                            <input name="total_questions" id="total_questions" type="text" wire:model.defer="total_questions" class="mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                            <button wire:click="changeSheets" id="changeSheets"
                                    class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-500 focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" > 문제지 생성 </button>
                            @if(!$is_modify)
                            <button id="showExternAnswerInputs"
                                    @click="showUploadFile=!showUploadFile"
                                    class="m-4 inline-flex items-center px-4 py-2 bg-yellow-300 hover:bg-yellow-500 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-sm text-black uppercase " > 문제지 업로드 </button>
                            @endif
                        </div>
                        @if(!$is_modify)
                        <div style="display:none;" x-show="showUploadFile" >
                            <form wire:submit.prevent="parseFile"  method="POST" action="" enctype="multipart/form-data">
                                <spam class="font-bold text-blue-500 text-xm">CSV 파일을 선택하고, [문제지 생성] 버튼을 눌러 주세요.</spam>
                                <input name="csv_file" id="csv_file" wire:model.lazy="csv_file" type="file"
                                       class="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 hover:bg-gray-100 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none"
                                       required>
                                @error('csv_file')<div class="text-red-400 text-sm font-extrabold">csv 파일만 업로드 가능합니다.</div>@enderror
                                @if (session()->has('error'))
                                    @php
                                        $message = Session::get('error');
                                    @endphp
                                    <div class="text-red-400 text-sm font-extrabold">{{ $message }}</div>
                                @endif
                                <button id="hideExternAnswerInputs"
                                        @click="showUploadFile=false"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent text-xs rounded-md  font-semibold text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25" > {{' 취   소 '}}</button>
                                <button type="submit" id="makeAnswerSheets"
                                        @click="showUploadFile=!showUploadFile"
                                        class="m-4 inline-flex items-center px-4 py-2 bg-sky-300 hover:bg-sky-500 active:bg-violet-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-sm text-black uppercase " >
                                    {{ ' 문제지 생성 ' }} </button>
                            </form>
                        </div>
                        @endif
                        <div class="inline-flex items-center">
                            <span class="text-gray-700">문제당 반복 테스트</span>
                            <input name="retry_answer" type="text" size="3" wire:model="retry_answer" class="mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                            <span class="text-gray-700">회</span>
                            <span class="mx-4"></span>
                            <span class="text-gray-700">문제 시작 번호</span>
                            <input name="question_start_no" type="text" size="5" wire:model.defer="question_start_no" class="inline-flex items-center mt-1 ml-2 text-xs block bg-gray-200 border-transparent focus:border-gray-500 focus:bg-white focus:ring-0" />
                            <button wire:click="updateQuestionStartNo" id="updatedQuestionStartNo"
                                    class="m-4 inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" > {{' 변경 '}} </button>
                        </div>
{{--                        </form>--}}
                        <div class="inline-flex items-center">
                            <span class="text-gray-700r">일괄 문장 분할 &nbsp;:&nbsp;</span>
                            <input name="split_count" type="radio" id="split_0" value="0" checked /><label for="split_0">&nbsp; 없음 &nbsp;</label> /&nbsp;
                            <input name="split_count" type="radio" id="split_1" value="1" /><label for="split_1">&nbsp; 1단어 &nbsp;</label> /&nbsp;
                            <input name="split_count" type="radio" id="split_2" value="2" /><label for="split_2">&nbsp; 2단어 &nbsp;</label> /&nbsp;
                            <input name="split_count" type="radio" id="split_3" value="3" /><label for="split_3">&nbsp; 3단어 &nbsp;</label> &nbsp;&nbsp;
                            <button onclick="splitAllAnswers(); return false;" id="splitAnswers"
                                    class="m-4 inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 outline-none focus:outline-none focus:ring focus:ring-violet-300 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest" > {{' 분할 하기 '}} </button>
                        </div>

                        @if(count($questions) > 0)
                        <form wire:submit.prevent="saveQuestions">
                            <table class="min-w-full border border-slate-400">
                                <tr>
                                    <th class="border border-slate-400 bg-blue-100 border px-8 py-4" style="width: 150px;">번호</th>
                                    <th class="border border-slate-400 bg-blue-100 border px-8 py-4">문 제</th>
    {{--                                <th class="border border-slate-400 bg-blue-100 border px-8 py-4">답안(단락 구분은 '/' 문자)</th>--}}
                                    <th class="border border-slate-400 bg-blue-100 border px-8 py-4" style="width: 100px;">&nbsp;</th>
                                </tr>
                                @foreach($questions as $index => $question)
                                <tr>
                                    <td rowspan="3" class="border border-slate-400 text-center bg-green-100">
                                    <input type="text" wire:model="questions.{{$index}}.question_no" name="questions.{{$index}}.question_no"
                                           value="{{$question['question_no']}}" class="w-11/12 mx-2"/>
                                    </td>
                                    <td class="flex align-middle border border-slate-400 justify-items-start">
                                        해석 :<textarea rows="2" type="text" wire:model="questions.{{$index}}.title"
                                                  name="questions.{{$index}}.title" class="w-11/12 mx-3">{{old($question["title"])}}</textarea>
                                    </td>
                                    <td rowspan="3" class="justify-items-start border border-slate-400">
                                        <button wire:click="deleteQuestion({{$index}})" class="m-4 inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition" > 삭 제 </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="flex align-middle border border-slate-100 justify-items-start text-center">
                                        <span class="text-gray-700r">문장 분할 &nbsp;:&nbsp;</span>
                                        <input onclick="" name="split_count_{{$index}}" type="radio" id="{{$index}}_split_1" value="1" />&nbsp;<label for="{{$index}}_split_1"> 1단어 </label> &nbsp;/&nbsp;
                                        <input onclick="" name="split_count_{{$index}}" type="radio" id="{{$index}}_split_2" value="2" />&nbsp;<label for="{{$index}}_split_2"> 2단어 </label> &nbsp;/&nbsp;
                                        <input onclick="" name="split_count_{{$index}}" type="radio" id="{{$index}}_split_3" value="3" />&nbsp;<label for="{{$index}}_split_3"> 3단어 </label> &nbsp;&nbsp;
                                        <button onclick="splitSentence({{$index}}); return false;"
                                                class="inline-flex items-center px-4 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-red-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                                        > 분 할 </button>&nbsp;
                                        <span class="text-gray-600">( <span class="text-red-400">선택한 단어수 기준으로 아래 문장을 분할합니다.</span> )</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="flex align-middle border border-slate-100 justify-items-start text-center">
                                        문제 :<textarea rows="2" type="text" wire:model.defer="questions.{{$index}}.answer.0.answer" answer_id="{{$question["answer"][0]['answer_id']}}"
                                              placeholder="I / am / a boy."
                                              name="questions.{{$index}}.answer.0" id="questions.{{$index}}.answer"  class="w-11/12 mx-3">{{old($question["answer"][0]['answer'])}}</textarea>
                                    </td>
                                </tr>

                                @endforeach
                            </table>

                            <div class="flex items-center justify-start mt-4">
                                <a href="{{route('detailSection', $section->id)}}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent text-xs rounded-md font-semibold text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">Back</a>
                                <x-jet-button type="submit" class="text-center ml-4 bg-blue-500">
                                    {{ __('  저  장  ') }}
                                </x-jet-button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function splitAllAnswers() {
            const wordCountObj = document.querySelector('input[name="split_count"]:checked');
            if (wordCountObj == null) return false;

            const wordCount = wordCountObj.value;

            const totalQuestionCount = parseInt(document.getElementById('total_questions').value);
            for (var i = 0; i < totalQuestionCount; i++) {
                splitWords(i, wordCount);
            }
        }

        function splitSentence(questionIndex) {
            // 선택된 단어 개수
            const radioName = `split_count_${questionIndex}`;
            const wordCountObj = document.querySelector(`input[name="${radioName}"]:checked`);
            if (wordCountObj == null) return false;

            const wordCount = wordCountObj.value;

            splitWords(questionIndex, wordCount);
        }
        function splitWords(questionIndex, wordCount) {
            try {
// 입력된 영어 문장
                let text = document.getElementById(`questions.${questionIndex}.answer`).value;
                // null 또는 빈 문자열 체크
                if (text === null || text.trim() === "") {
                    console.log("The input is either null or an empty string.");
                    return false;
                }
                // '/'와 그 주위의 공백을 제거
                text = text.replace(/\/\s*/g, "");

                // 입력된 문장을 공백으로 분할
                const words = text.split(" ");

                // 결과 배열을 만들기
                let result = [];

                // 지정된 단어 수만큼 묶어서 "/"를 추가
                for (let i = 0; i < words.length; i += parseInt(wordCount)) {
                    result.push(words.slice(i, i + parseInt(wordCount)).join(" "));
                }

                // "/"를 삽입하여 문자열로 변환
                const output = result.join(" / ");

                // 결과를 출력
                document.getElementById(`questions.${questionIndex}.answer`).value = output;
                @this.set(`questions.${questionIndex}.answer.0.answer`, output);
                // Livewire.find('{{ $this->id }}').set(`questions.${questionIndex}.answer`, output);
            } catch (e) {
                console.log(e);
            }

            return false;
        }
    </script>
</div>
