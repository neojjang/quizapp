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
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
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
                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h2 class="title-font font-medium text-xl text-gray-900">{{$sectionCount}}</h2>
                            <p class="leading-relaxed">시험 리스트</p>
                        </div>
                    </a>
                </div>

                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <div class="border-2 border-gray-200 px-4 py-6 rounded-lg bg-white">
                        <svg fill=" none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="text-indigo-500 w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"></path>
                        </svg>
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
