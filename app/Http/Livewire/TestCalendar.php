<?php

namespace App\Http\Livewire;

use App\Models\Section;
use App\Models\QuizHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class TestCalendar extends Component
{
    public $year;
    public $month;

    public $target_day;

    public $testers = [];

    public function mount($target_date=null)
    {
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->month;
        $this->target_day = Carbon::now()->day;

        if (isset($target_date)) {
            $date = Carbon::createFromFormat('Y-m-d', $target_date);
            $this->year = $date->year;
            $this->month = $date->month;
            $this->target_day = $date->day;
        }
        $this->loadTesters();
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->loadTesters();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->loadTesters();
    }

    public function refresh()
    {
        $this->loadTesters();
    }

    public function getCalendarDays()
    {
        $firstDayOfMonth = Carbon::create($this->year, $this->month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $dayOfWeek = $firstDayOfMonth->dayOfWeek;

        $calendarDays = [];
        // 이전 달의 빈 날짜 채우기
        for ($i = 0; $i < $dayOfWeek; $i++) {
            $calendarDays[] = null;
        }
        // 이번 달의 날자 채우기
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $calendarDays[] = Carbon::create($this->year, $this->month, $i);
        }

        return collect($calendarDays);
    }

    public function getMonthName()
    {
        return Carbon::create($this->year, $this->month)->translatedFormat('Y F');
    }

    public function render()
    {
        return view('livewire.test-calendar', [
            'calendarDays' => $this->getCalendarDays(),
            'monthName' => $this->getMonthName(),
            ]);
    }

    public function loadTesters()
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $this->testers = QuizHeader::select(
            DB::raw('DATE(created_at) as test_date'),
            DB::raw('count(*) as count') // 해당 날짜의 테스트 완료 학생 수
        )->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('completed', '1')
            -> groupBy('test_date')->get()
            ->pluck( 'count', 'test_date'); // 결과를 ['Y-m-d' => count] 형식으로 변환
//        $quizHeaders = QuizHeader::select(
//            DB::raw('DATE(created_at) as test_date'),
//            DB::raw('section_id'),
//            DB::raw('count(*) as count') // 해당 날짜의 테스트 완료 학생 수
//        )->whereBetween('created_at', [$startOfMonth, $endOfMonth])
//            ->where('completed', '1')
//            -> groupBy('test_date', 'section_id')->get();
//        $this->testers = collect($quizHeaders)->groupBy('test_date')->transform(function($items, $key) {
//            $data = collect($items)->transform(function($item) {
//                $item['section'] = Section::query()->where('id', $item->section_id)->first();
//                return $item;
//            });
//            return $data;
//        });

    }
}
