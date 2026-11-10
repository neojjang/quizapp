<?php

namespace Tests\Feature\Quiz;

use App\Constants\Section as SectionType;
use App\Models\QuizHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** 관리자·학생 홈 차트 데이터 */
class ChartDataTest extends TestCase
{
    use RefreshDatabase;
    use BuildsQuizzes;

    private function takeQuizzes($user, array $scores, bool $completed = true): void
    {
        $section = $this->makeSection(SectionType::OMR);
        foreach ($scores as $i => $score) {
            QuizHeader::create([
                'user_id' => $user->id, 'section_id' => $section->id, 'quiz_size' => 1,
                'completed' => $completed, 'score' => $score,
                'created_at' => Carbon::parse('2026-01-01')->addDays($i),
            ]);
        }
    }

    public function test_student_chart_shows_own_latest_30_completed_scores_in_order()
    {
        $student = $this->makeUser();
        $this->takeQuizzes($student, range(1, 35));
        $this->takeQuizzes($student, [99], false);
        $this->takeQuizzes($this->makeUser(), [77]);

        $chart = $this->actingAs($student)->get(route('userQuizHome'))
            ->assertOk()
            ->assertSee('<canvas', false)
            ->viewData('scoreChart');

        $this->assertEquals(range(6, 35), $chart['data']->all());
        $this->assertEquals('01-06', $chart['labels']->first());
        $this->assertEquals('02-04', $chart['labels']->last());
    }

    public function test_admin_chart_shows_latest_100_scores_and_30_days_of_signups()
    {
        Carbon::setTestNow('2026-11-10 12:00:00');
        $admin = $this->makeUser('admin');
        $this->takeQuizzes($this->makeUser(), range(1, 105));
        $this->makeUser()->forceFill(['created_at' => now()->subDays(3)])->save();
        $this->makeUser()->forceFill(['created_at' => now()->subDays(40)])->save();

        $response = $this->actingAs($admin)->get(route('adminhome'))->assertOk();

        $this->assertEquals(range(6, 105), $response->viewData('scoreChart')['data']->all());

        $userChart = $response->viewData('userChart');
        $this->assertCount(30, $userChart['labels']);
        $this->assertEquals('10-12', $userChart['labels']->first());
        $this->assertEquals('11-10', $userChart['labels']->last());
        $this->assertEquals(1, $userChart['data'][26]);  // 11-07 가입 1명
        $this->assertEquals(3, $userChart['data'][29]);  // 오늘: admin, 응시자, 콘텐츠 작성자
        $this->assertEquals(4, $userChart['data']->sum());
    }
}
