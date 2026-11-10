<?php

namespace Tests\Feature\Quiz;

use App\Http\Livewire\UserQuizlv;
use App\Models\Quiz;
use App\Models\QuizHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** 일반형 시험: 한 문제씩 제출(nextQuestion)하며 자동 채점 */
class NormalQuizTest extends TestCase
{
    use RefreshDatabase;
    use BuildsQuizzes;

    private function start($section)
    {
        return Livewire::actingAs($this->makeUser())
            ->test(UserQuizlv::class, $this->mountParams($section))
            ->set('sectionId', $section->id)
            ->call('startQuiz');
    }

    public function test_selective_correct_answer_scores_100()
    {
        $this->markTestIncomplete('B1/B2: 일반형 시험의 선택형 문제 제출 시 TypeError (docs/upgrade/PLAN.md 8장)');
        $section = $this->makeSection();
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);

        $this->start($section)
            ->set('userAnswered', [$this->choiceValue($q, 0)])
            ->call('nextQuestion')
            ->assertSet('showResult', true);

        $header = QuizHeader::first();
        $this->assertEquals(100, $header->score);
        $this->assertEquals(1, $header->completed);
        $this->assertSame([$q->id], unserialize($header->questions_taken));
        $this->assertEquals('1', Quiz::first()->is_correct);
    }

    public function test_selective_wrong_answer_scores_0()
    {
        $this->markTestIncomplete('B1/B2: 일반형 시험의 선택형 문제 제출 시 TypeError (docs/upgrade/PLAN.md 8장)');
        $section = $this->makeSection();
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);

        $this->start($section)
            ->set('userAnswered', [$this->choiceValue($q, 1)])
            ->call('nextQuestion');

        $this->assertEquals(0, QuizHeader::first()->score);
        $this->assertEquals('0', Quiz::first()->is_correct);
    }

    public function test_translation_exact_match_is_correct()
    {
        $section = $this->makeSection();
        // 공백만 다른 정답은 Python 채점 없이 정답 처리
        $this->makeQuestion($section, 2, [['나는 소년이다', true]]);

        $this->start($section)
            ->set('userAnswered', '나는  소년 이다')
            ->call('nextQuestion');

        $this->assertEquals('1', Quiz::first()->is_correct);
        $this->assertEquals(100, QuizHeader::first()->score);
    }

    public function test_writing_ignores_case_and_punctuation_spacing()
    {
        $section = $this->makeSection();
        $this->makeQuestion($section, 3, [['I am a boy.', true]]);

        $this->start($section)
            ->set('userAnswered', '  i  AM a boy.')
            ->call('nextQuestion');

        $this->assertEquals('1', Quiz::first()->is_correct);
    }

    public function test_multiple_questions_are_asked_in_order_and_scored()
    {
        $section = $this->makeSection();
        $q1 = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $q2 = $this->makeQuestion($section, 3, [['I am a boy.', true]]);

        $this->start($section)
            ->assertSet('currentQuestion.id', $q1->id)
            ->set('userAnswered', '나는 소년이다')
            ->call('nextQuestion')
            ->assertSet('currentQuestion.id', $q2->id)
            ->assertSet('showResult', false)
            ->set('userAnswered', 'You are a girl.')
            ->call('nextQuestion')
            ->assertSet('showResult', true);

        $this->assertEquals(50, QuizHeader::first()->score);
        $this->assertSame(2, Quiz::count());
        $this->assertEquals([$q1->id, $q2->id], Quiz::orderBy('id')->pluck('question_id')->all());
    }

    public function test_short_answer_is_graded()
    {
        $this->markTestIncomplete('B7: 일반형 시험의 단답형 문제는 채점 분기가 없어 제출 시 오류 (docs/upgrade/PLAN.md 8장)');
        $section = $this->makeSection();
        $this->makeQuestion($section, 4, [['Apple', true]]);

        $this->start($section)
            ->set('userAnswered', 'apple')
            ->call('nextQuestion');

        $this->assertEquals('1', Quiz::first()->is_correct);
    }
}
