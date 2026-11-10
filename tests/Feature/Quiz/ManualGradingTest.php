<?php

namespace Tests\Feature\Quiz;

use App\Constants\Section as SectionType;
use App\Http\Livewire\UserAnswer;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** 관리자 수동 채점: 정오 변경 시 점수 재계산, 채점 화면 열람 시 검토 표시 */
class ManualGradingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsQuizzes;

    public function test_changing_a_result_recalculates_the_score()
    {
        $section = $this->makeSection(SectionType::OMR);
        $q1 = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $q2 = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);
        $header = $this->makeTakenQuiz($this->makeUser(), $section, [
            [$q1, '2', '나는 소녀이다'],
            [$q2, '1', $q2->answers[0]->id],
        ]);
        $pending = Quiz::where('question_id', $q1->id)->first();

        Livewire::actingAs($this->owner)
            ->test(UserAnswer::class, ['userAnswer' => $pending, 'question' => $q1, 'quizHeader' => $header])
            ->call('changeUserAnswer', '1');

        $this->assertEquals('1', $pending->fresh()->is_correct);
        $this->assertEquals(100, $header->fresh()->score);

        Livewire::actingAs($this->owner)
            ->test(UserAnswer::class, ['userAnswer' => $pending->fresh(), 'question' => $q1, 'quizHeader' => $header->fresh()])
            ->call('changeUserAnswer', '0');

        $this->assertEquals(50, $header->fresh()->score);
    }

    public function test_changing_a_choice_question_result_keeps_the_stored_answer()
    {
        $this->markTestIncomplete('B6: 선택형 문항의 정오 변경 후 다시 그릴 때 TypeError (docs/upgrade/PLAN.md 8장)');
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);
        $answerIds = $q->answers[0]->id . ',' . $q->answers[1]->id;
        $header = $this->makeTakenQuiz($this->makeUser(), $section, [[$q, '0', $answerIds]]);
        $quiz = Quiz::first();

        Livewire::actingAs($this->owner)
            ->test(UserAnswer::class, ['userAnswer' => $quiz, 'question' => $q, 'quizHeader' => $header])
            ->call('changeUserAnswer', '1');

        $this->assertEquals($answerIds, $quiz->fresh()->user_answer);
        $this->assertEquals(100, $header->fresh()->score);
    }

    public function test_opening_the_grading_screen_marks_the_quiz_reviewed()
    {
        // 현재 동작 기록: 화면을 열기만 해도 reviewed=true (개선 후보 B5)
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $header = $this->makeTakenQuiz($this->makeUser(), $section, [[$q, '2', '나는 소녀이다']]);

        $this->actingAs($this->owner)
            ->get(route('scoreQuestion', [$section, $header]))
            ->assertOk()
            ->assertSee('나는 소녀이다');

        $this->assertEquals(1, $header->fresh()->reviewed);
    }
}
