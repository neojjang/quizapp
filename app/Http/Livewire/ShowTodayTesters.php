<?php

namespace App\Http\Livewire;

use App\Models\QuizHeader;
use Carbon\Carbon;
use Livewire\Component;

class ShowTodayTesters extends Component
{
    public $testers;

    public function mount()
    {
        $this->loadTodayTesters();
    }
    public function render()
    {
        return view('livewire.show-today-testers');
    }

    public function loadTodayTesters()
    {
        $this->testers = QuizHeader::query()->where('completed', '1')
        ->where('reviewed', '0')
            ->whereBetween('created_at', [
                Carbon::today()->subDays(2)->startOfDay(), // 그저께의 시작 시간
                Carbon::today()->endOfDay() // 오늘의 마지막 시간
            ])
            ->orderBy('created_at', 'desc')
        ->take(30)->get();

    }
}
