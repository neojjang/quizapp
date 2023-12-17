<x-app-layout>
    <x-slot name="header">
        <div class="md:flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('수업 Home') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl m-4 mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mx-auto">
                <div class="flex justify-between items-center py-4">
                    <a href="{{route('createClassRoom')}}" class="tracking-wide font-bold rounded border-2 border-blue-500 hover:border-blue-500 bg-blue-500 text-white hover:bg-blue-600 transition shadow-md py-2 px-6 items-center">수업 생성</a>
                    <a href="{{route('adminhome')}}" class="tracking-wide font-bold rounded border-2 border-blue-500 hover:border-blue-500 bg-blue-500 text-white hover:bg-blue-600 transition shadow-md py-2 px-6 items-center">Back</a>
                </div>
                @if($classRooms->isEmpty())
                <div class="px-4 py-5 sm:px-6">
                    <h1 class="text-sm leading-6 font-medium text-gray-900">
                        등록 한 수업이 없습니다.
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        수업을 등록하면 리스트를 볼 수 있습니다.
                    </p>
                </div>
                @else
                <!-- --------------------- START NEW TABLE --------------------->
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="tracking-wide font-bold rounded border-2 bg-green-500 text-white  transition shadow-md py-2 px-6 items-center">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                                Published
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                                                Sections
                                            </th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Edit</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="capitalize bg-white divide-y divide-gray-200">
                                        @foreach($classRooms as $section)
                                        <tr class="hover:bg-green-100">
                                            <td class="px-6 ">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <a class="text-blue-400 hover:underline" href="{{ route('detailClassRoom', $section->id) }}">
                                                                {{ $section->name}}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-1">
                                                <div class="text-sm text-gray-900">{{ $section->is_active === '1'  ? 'Yes' : 'No' }}</div>
                                            </td>
                                            <td class="px-6 py-1">
                                                <div class="text-sm text-gray-900">{{ $section->sections_count }}</div>
                                            </td>
                                            <td class="sm:flex align-middle justify-center items-center px-6 py-1 text-right text-sm font-medium">
                                                <a href="{{ route('createSection', $section->id )}}" class="text-green-500 hover:text-green-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="text-blue-500 hover:text-blue-700 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                                <a href="{{ route('editClassRoom', $section->id )}} " class="text-green-500 hover:text-green-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                                <form action="{{route('deleteClassRoom',$section->id)}}" method="post" onsubmit="return deleteClassRoom('{{ $section->name}}'); return false;">
                                                    @csrf
                                                    <a class="text-red-500 hover:text-red-700">
                                                        <button type="submit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 pt-1" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </a>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $classRooms->links() }}
                        </div>
                    </div>
                </div>
                @endif
                <!-- ---------------- END NEW TABLE --------------------- -->
            </div>
        </div>
    </div>
    <!-- Modal toggle -->
    @push('js')
        <script type="text/javascript">
            function deleteClassRoom(title) {
                return confirm(`[${title}] 수업을 삭제 하시겠습니까?`);
                // console.log(form);
                // Swal.fire({
                //     "title":"\uc218\uc5c5 \uc0ad\uc81c",
                //     "text":"\uc218\uc5c5\uc744 \uc0ad\uc81c \ud558\uc2dc\uaca0\uc2b5\ub2c8\uae4c?",
                //     "background":"#fff","width":"25rem","heightAuto":true,"padding":"1.25rem",
                //     "showCloseButton":false,
                //     "confirmButtonText":"Yes, delete it!",
                //     "cancelButtonText":"Cancel",
                //     "timerProgressBar":false,
                //     "customClass":{
                //         "container":null,
                //         "popup":null,
                //         "header":null,
                //         "title":null,"closeButton":null,"icon":null,"image":null,
                //         "content":null,"input":null,"actions":null,
                //         "confirmButton":null,"cancelButton":null,"footer":null
                //     },
                //     "showCancelButton":true,
                //     "confirmButtonColor":null,"icon":"warning",
                //     "showLoaderOnConfirm":true,"allowEscapeKey":false,
                //     "allowOutsideClick":false
                // }).then(function(result) {
                //     if (result.isConfirmed) {
                //         form.submit();
                //     }
                // });
                // return false;
            }
        </script>
    @endpush
</x-app-layout>
