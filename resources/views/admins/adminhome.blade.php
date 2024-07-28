<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Home') }}
        </h2>
    </x-slot>
    <section class="text-gray-600 body-font">
        <div class="container px-5 py-5 mx-auto">
            <div class="flex flex-wrap -m-4 text-center ">
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full ">
                    <a href="{{route('listMajorGroups')}}">
                        <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white hover:bg-green-100">
{{--                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">--}}
{{--                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>--}}
{{--                            </svg>--}}
                            <svg class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="si-glyph si-glyph-file-box" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>862</title> <defs> </defs> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g transform="translate(1.000000, 0.000000)" fill="#434343"> <path d="M13.993,0.006 L2.072,0.006 L0.012,8.761 L0.012,13.931 C0.012,15.265 0.484,15.959 1.816,15.959 L14.096,15.959 C15.342,15.959 15.981,15.432 15.981,13.848 L15.981,8.761 L13.993,0.006 L13.993,0.006 Z M10.016,10 L5.985,10 L5.985,8.969 L10.016,8.969 L10.016,10 L10.016,10 Z M1.505,8.01 L2.912,0.981 L13.177,0.981 L14.54,8.01 L1.505,8.01 L1.505,8.01 Z" class="si-glyph-fill"> </path> <rect x="4" y="4" width="7.947" height="0.968" class="si-glyph-fill"> </rect> <rect x="5" y="2" width="5.947" height="0.968" class="si-glyph-fill"> </rect> <rect x="3" y="6" width="9.951" height="0.965" class="si-glyph-fill"> </rect> </g> </g> </g></svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$majorGroupCount}}</h2>
                            <p class="leading-relaxed">수업 대분류</p>
                        </div>
                    </a>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full ">
                    <a href="{{route('listMediumGroups')}}">
                        <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white hover:bg-green-100">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$mediumGroupCount}}</h2>
                            <p class="leading-relaxed">수업 중분류</p>
                        </div>
                    </a>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full ">
                    <a href="{{route('listClassRoom')}}">
                        <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white hover:bg-green-100">
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$classRoomCount}}</h2>
                            <p class="leading-relaxed">수업 리스트</p>
                        </div>
                    </a>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full ">
                    <a href="{{route('listSection')}}">
                        <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white hover:bg-green-100">
{{--                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">--}}
{{--                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>--}}
{{--                            </svg>--}}
                            <svg height="200px" width="200px" class="text-indigo-500 w-12 h-12 mb-3 inline-block"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 470 470" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path style="fill:#98D9D5;" d="M426.126,455h2.5V45h-2.5c-4.143,0-7.5-3.357-7.5-7.5h-15v425h15 C418.626,458.357,421.983,455,426.126,455z"></path> <path style="fill:#C1E8E6;" d="M308.554,455h80.072V45h-2.5c-4.143,0-7.5-3.357-7.5-7.5h-15v340.934 c0,2.428-1.093,4.531-2.798,5.905l-78.16,78.161h18.386C301.054,458.357,304.411,455,308.554,455z"></path> <polygon style="fill:#98D9D5;" points="279.626,386 279.626,444.328 337.953,386 "></polygon> <path style="fill:#FFFFFF;" d="M264.626,378.5c0-4.143,3.357-7.5,7.5-7.5h76.5V45H141.02v52.5c0,15.163-12.337,27.5-27.5,27.5 s-27.5-12.337-27.5-27.5v-30c0-4.143,3.357-7.5,7.5-7.5s7.5,3.357,7.5,7.5v30c0,6.893,5.607,12.5,12.5,12.5s12.5-5.607,12.5-12.5 V45H41.374v410h223.252V378.5z M93.874,150.5h202.252c4.143,0,7.5,3.357,7.5,7.5s-3.357,7.5-7.5,7.5H93.874 c-4.143,0-7.5-3.357-7.5-7.5S89.731,150.5,93.874,150.5z M93.874,195.5h202.252c4.143,0,7.5,3.357,7.5,7.5s-3.357,7.5-7.5,7.5 H93.874c-4.143,0-7.5-3.357-7.5-7.5S89.731,195.5,93.874,195.5z M93.874,240.5h202.252c4.143,0,7.5,3.357,7.5,7.5 s-3.357,7.5-7.5,7.5H93.874c-4.143,0-7.5-3.357-7.5-7.5S89.731,240.5,93.874,240.5z M93.874,285.5h202.252 c4.143,0,7.5,3.357,7.5,7.5s-3.357,7.5-7.5,7.5H93.874c-4.143,0-7.5-3.357-7.5-7.5S89.731,285.5,93.874,285.5z M86.374,338 c0-4.143,3.357-7.5,7.5-7.5h202.252c4.143,0,7.5,3.357,7.5,7.5s-3.357,7.5-7.5,7.5H93.874C89.731,345.5,86.374,342.143,86.374,338z "></path> <path style="fill:#082947;" d="M363.626,378.5c0-0.066,0-341,0-341c0-4.143-3.357-7.5-7.5-7.5H139.141 C133.79,12.647,117.605,0,98.52,0C79.742,0,63.311,12.514,57.906,30H33.874c-4.143,0-7.5,3.357-7.5,7.5v425 c0,4.143,3.357,7.5,7.5,7.5h238.187c2.425,0,4.527-1.092,5.902-2.794l4.706-4.706l78.16-78.161 C362.533,382.965,363.626,380.861,363.626,378.5z M41.374,45h84.646v52.5c0,6.893-5.607,12.5-12.5,12.5s-12.5-5.607-12.5-12.5v-30 c0-4.143-3.357-7.5-7.5-7.5s-7.5,3.357-7.5,7.5v30c0,15.163,12.337,27.5,27.5,27.5s27.5-12.337,27.5-27.5V45h207.606v326h-76.5 c-4.143,0-7.5,3.357-7.5,7.5V455H41.374V45z M279.626,386h58.327l-58.327,58.328V386z M98.52,15c10.663,0,19.922,6.105,24.482,15 H74.036C78.64,21.052,88.011,15,98.52,15z"></path> <path style="fill:#082947;" d="M296.126,345.5c4.143,0,7.5-3.357,7.5-7.5s-3.357-7.5-7.5-7.5H93.874c-4.143,0-7.5,3.357-7.5,7.5 s3.357,7.5,7.5,7.5H296.126z"></path> <path style="fill:#082947;" d="M93.874,300.5h202.252c4.143,0,7.5-3.357,7.5-7.5s-3.357-7.5-7.5-7.5H93.874 c-4.143,0-7.5,3.357-7.5,7.5S89.731,300.5,93.874,300.5z"></path> <path style="fill:#082947;" d="M93.874,255.5h202.252c4.143,0,7.5-3.357,7.5-7.5s-3.357-7.5-7.5-7.5H93.874 c-4.143,0-7.5,3.357-7.5,7.5S89.731,255.5,93.874,255.5z"></path> <path style="fill:#082947;" d="M93.874,210.5h202.252c4.143,0,7.5-3.357,7.5-7.5s-3.357-7.5-7.5-7.5H93.874 c-4.143,0-7.5,3.357-7.5,7.5S89.731,210.5,93.874,210.5z"></path> <path style="fill:#082947;" d="M93.874,165.5h202.252c4.143,0,7.5-3.357,7.5-7.5s-3.357-7.5-7.5-7.5H93.874 c-4.143,0-7.5,3.357-7.5,7.5S89.731,165.5,93.874,165.5z"></path> <path style="fill:#082947;" d="M396.126,30h-10c-4.143,0-7.5,3.357-7.5,7.5s3.357,7.5,7.5,7.5h2.5v410h-80.072 c-4.143,0-7.5,3.357-7.5,7.5s3.357,7.5,7.5,7.5h87.572c4.143,0,7.5-3.357,7.5-7.5v-425C403.626,33.357,400.269,30,396.126,30z"></path> <path style="fill:#082947;" d="M436.126,30h-10c-4.143,0-7.5,3.357-7.5,7.5s3.357,7.5,7.5,7.5h2.5v410h-2.5 c-4.143,0-7.5,3.357-7.5,7.5s3.357,7.5,7.5,7.5h10c4.143,0,7.5-3.357,7.5-7.5v-425C443.626,33.357,440.269,30,436.126,30z"></path> </g> </g></svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$sectionCount}}</h2>
                            <p class="leading-relaxed">시험 리스트</p>
                        </div>
                    </a>
                </div>

                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white">
{{--                        <svg fill=" none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">--}}
{{--                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"></path>--}}
{{--                        </svg>--}}
                        <svg fill="#000000" height="200px" width="200px" class="text-indigo-500 w-12 h-12 mb-3 inline-block"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 463 463" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M367.5,32h-57.734c-3.138-9.29-11.93-16-22.266-16h-24.416c-7.41-9.965-19.148-16-31.584-16 c-12.435,0-24.174,6.035-31.585,16H175.5c-10.336,0-19.128,6.71-22.266,16H95.5C78.131,32,64,46.131,64,63.5v368 c0,17.369,14.131,31.5,31.5,31.5h272c17.369,0,31.5-14.131,31.5-31.5v-368C399,46.131,384.869,32,367.5,32z M175.5,87h112 c7.023,0,13.332-3.101,17.641-8H352v337H111V79h46.859C162.168,83.899,168.477,87,175.5,87z M175.5,31h28.438 c2.67,0,5.139-1.419,6.482-3.727C214.893,19.588,222.773,15,231.5,15c8.728,0,16.607,4.588,21.079,12.272 c1.343,2.308,3.813,3.728,6.482,3.728H287.5c4.687,0,8.5,3.813,8.5,8.5v24c0,4.687-3.813,8.5-8.5,8.5h-112 c-4.687,0-8.5-3.813-8.5-8.5v-24C167,34.813,170.813,31,175.5,31z M384,431.5c0,9.098-7.402,16.5-16.5,16.5h-272 c-9.098,0-16.5-7.402-16.5-16.5v-368C79,54.402,86.402,47,95.5,47H152v16.5c0,0.168,0.009,0.333,0.013,0.5H103.5 c-4.143,0-7.5,3.358-7.5,7.5v352c0,4.142,3.357,7.5,7.5,7.5h256c4.143,0,7.5-3.358,7.5-7.5v-352c0-4.142-3.357-7.5-7.5-7.5h-48.513 c0.004-0.167,0.013-0.332,0.013-0.5V47h56.5c9.098,0,16.5,7.402,16.5,16.5V431.5z"></path> <path d="M231.5,47c1.979,0,3.91-0.8,5.3-2.2c1.4-1.39,2.2-3.33,2.2-5.3c0-1.97-0.8-3.91-2.2-5.3c-1.39-1.4-3.32-2.2-5.3-2.2 c-1.98,0-3.91,0.8-5.3,2.2c-1.4,1.39-2.2,3.32-2.2,5.3s0.8,3.91,2.2,5.3C227.59,46.2,229.52,47,231.5,47z"></path> <path d="M183.5,159h136c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-136c-4.143,0-7.5,3.358-7.5,7.5S179.357,159,183.5,159z"></path> <path d="M183.5,239h136c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-136c-4.143,0-7.5,3.358-7.5,7.5S179.357,239,183.5,239z"></path> <path d="M183.5,319h24c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-24c-4.143,0-7.5,3.358-7.5,7.5S179.357,319,183.5,319z"></path> <path d="M183.5,199h136c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-136c-4.143,0-7.5,3.358-7.5,7.5S179.357,199,183.5,199z"></path> <path d="M183.5,279h32c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-32c-4.143,0-7.5,3.358-7.5,7.5S179.357,279,183.5,279z"></path> <path d="M183.5,359h32c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-32c-4.143,0-7.5,3.358-7.5,7.5S179.357,359,183.5,359z"></path> <path d="M143.5,159h8c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-8c-4.143,0-7.5,3.358-7.5,7.5S139.357,159,143.5,159z"></path> <path d="M143.5,239h8c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-8c-4.143,0-7.5,3.358-7.5,7.5S139.357,239,143.5,239z"></path> <path d="M143.5,319h8c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-8c-4.143,0-7.5,3.358-7.5,7.5S139.357,319,143.5,319z"></path> <path d="M143.5,199h8c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-8c-4.143,0-7.5,3.358-7.5,7.5S139.357,199,143.5,199z"></path> <path d="M143.5,279h8c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-8c-4.143,0-7.5,3.358-7.5,7.5S139.357,279,143.5,279z"></path> <path d="M143.5,359h8c4.143,0,7.5-3.358,7.5-7.5s-3.357-7.5-7.5-7.5h-8c-4.143,0-7.5,3.358-7.5,7.5S139.357,359,143.5,359z"></path> <path d="M279.5,264c-26.191,0-47.5,21.309-47.5,47.5s21.309,47.5,47.5,47.5c10.583,0,20.367-3.482,28.272-9.357 c0.074-0.052,0.155-0.088,0.228-0.143c0.2-0.15,0.389-0.309,0.57-0.474C319.771,340.329,327,326.747,327,311.5 C327,285.309,305.691,264,279.5,264z M272,279.883V304h-24.117C250.708,292.094,260.094,282.708,272,279.883z M247.883,319h27.867 l16.719,22.292c-3.976,1.737-8.36,2.708-12.969,2.708C264.161,344,251.279,333.315,247.883,319z M304.463,332.284L287,309v-29.117 c14.315,3.396,25,16.278,25,31.617C312,319.398,309.165,326.646,304.463,332.284z"></path> </g> </g></svg>
                        <h2 class="title-font font-medium text-xl text-gray-900">{{$questionCount}}</h2>
                        <p class="leading-relaxed">문제 리스트</p>
                    </div>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <a href="{{route('usersIndex')}}">
                        <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white hover:bg-green-100">
                            <svg fill=" none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"></path>
                            </svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$userCount}}</h2>
                            <p class="leading-relaxed">Users</p>
                        </div>
                    </a>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <a href="{{route('usersIndex')}}">
                        <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white hover:bg-green-100">
                            <svg fill="#000000" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier"> <path d="M13.5,20 C14.3284271,20 15,19.3284271 15,18.5 C15,17.1192881 16.1192881,16 17.5,16 C18.3284271,16 19,15.3284271 19,14.5 L19,11.5 C19,11.2238576 19.2238576,11 19.5,11 C19.7761424,11 20,11.2238576 20,11.5 L20,14.5 C20,18.0898509 17.0898509,21 13.5,21 L6.5,21 C5.11928813,21 4,19.8807119 4,18.5 L4,5.5 C4,4.11928813 5.11928813,3 6.5,3 L12.5,3 C12.7761424,3 13,3.22385763 13,3.5 C13,3.77614237 12.7761424,4 12.5,4 L6.5,4 C5.67157288,4 5,4.67157288 5,5.5 L5,18.5 C5,19.3284271 5.67157288,20 6.5,20 L13.5,20 L13.5,20 Z M15.7913481,19.5014408 C16.9873685,18.9526013 17.9526013,17.9873685 18.5014408,16.7913481 C18.1948298,16.9255432 17.8561101,17 17.5,17 C16.6715729,17 16,17.6715729 16,18.5 C16,18.8561101 15.9255432,19.1948298 15.7913481,19.5014408 L15.7913481,19.5014408 Z M18,6 L20.5,6 C20.7761424,6 21,6.22385763 21,6.5 C21,6.77614237 20.7761424,7 20.5,7 L18,7 L18,9.5 C18,9.77614237 17.7761424,10 17.5,10 C17.2238576,10 17,9.77614237 17,9.5 L17,7 L14.5,7 C14.2238576,7 14,6.77614237 14,6.5 C14,6.22385763 14.2238576,6 14.5,6 L17,6 L17,3.5 C17,3.22385763 17.2238576,3 17.5,3 C17.7761424,3 18,3.22385763 18,3.5 L18,6 Z M8.5,9 C8.22385763,9 8,8.77614237 8,8.5 C8,8.22385763 8.22385763,8 8.5,8 L12.5,8 C12.7761424,8 13,8.22385763 13,8.5 C13,8.77614237 12.7761424,9 12.5,9 L8.5,9 Z M8.5,12 C8.22385763,12 8,11.7761424 8,11.5 C8,11.2238576 8.22385763,11 8.5,11 L15.5,11 C15.7761424,11 16,11.2238576 16,11.5 C16,11.7761424 15.7761424,12 15.5,12 L8.5,12 Z M8.5,15 C8.22385763,15 8,14.7761424 8,14.5 C8,14.2238576 8.22385763,14 8.5,14 L13.5,14 C13.7761424,14 14,14.2238576 14,14.5 C14,14.7761424 13.7761424,15 13.5,15 L8.5,15 Z"></path>
                                </g>
                            </svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$answerNoteCount ?? 0}}</h2>
                            <p class="leading-relaxed">오답노트</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- Cards -->
    <section class="text-gray-600 body-font">
        <div class="container px-5 py-5 mx-auto">
            <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-2">
                <div class="flex items-center p-1 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <div class="p-4 w-full">
                        <div class="container px-5 mx-auto" id="chart">
                        </div>
                    </div>
                </div>
                <!-- Card -->
                <div class="flex items-center p-1 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <div class="p-4 w-full">
                        <div class="container px-5 mx-auto" id="chart2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @push('js')
    <script>
        const GlobalQuizChart = new Chartisan({
            el: '#chart',
            url: "@chart('global_quizzes')",
            loader: {
                color: '#ff00ff',
                size: [60, 60],
                type: 'bar',
                textColor: '#ffff00',
                text: 'Loading chart data...',
            },
            hooks: new ChartisanHooks()
                .colors()
                .beginAtZero()
                .title('Quiz Scores')
                .datasets(['line'])
                .stepSize(25)
                .responsive()
        });
    </script>
    <script>
        const MonthlyUserChart = new Chartisan({
            el: '#chart2',
            url: "@chart('monthly_users')",
            loader: {
                color: '#ff00ff',
                size: [60, 60],
                type: 'bar',
                textColor: '#ffff00',
                text: 'Loading chart data...',
            },
            hooks: new ChartisanHooks()
                .colors()
                .beginAtZero()
                .title('Monthly Users')
                .datasets(['line'])
                .stepSize(25)
                .responsive()
        });
    </script>
    @endpush
</x-app-layout>
