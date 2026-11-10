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
        $section = $this->makeSection();
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);

        $this->start($section)
            ->set('userAnswered', [$this->choiceValue($q, 1)])
            ->call('nextQuestion');

        $this->assertEquals(0, QuizHeader::first()->score);
        $this->assertEquals('0', Quiz::first()->is_correct);
    }

    public function test_choice_values_do_not_reveal_the_answer()
    {
        $section = $this->makeSection();
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);

        $this->start($section)
            ->assertSeeHtml('value="' . $q->answers[0]->id . '"')
            ->assertDontSeeHtml($q->answers[0]->id . ',1');
    }

    public function test_tampered_choice_values_are_graded_by_the_database()
    {
        $section = $this->makeSection();
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);
        $other = $this->makeQuestion($section, 1, [['다른 문제 정답', true]]);
        $wrongId = $q->answers[1]->id;

        // 예전 형식으로 오답에 정답 표시를 붙이거나, 다른 문제의 정답 ID·중복 ID를 섞어 보내도 정답이 되지 않는다
        $this->start($section)
            ->set('userAnswered', ["{$wrongId},1", (string) $other->answers[0]->id, "{$wrongId},1"])
            ->call('nextQuestion');

        $quiz = Quiz::where('question_id', $q->id)->first();
        $this->assertEquals('0', $quiz->is_correct);
        $this->assertEquals((string) $wrongId, $quiz->user_answer);
    }

    public function test_replayed_submission_moves_on_without_saving_again()
    {
        $section = $this->makeSection();
        $q1 = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);
        $q2 = $this->makeQuestion($section, 4, [['Apple', true]]);

        // 저장은 됐지만 응답이 유실된 경우: 브라우저는 제출 전 상태(replay)로 다시 보낸다
        $component = $this->start($section)->set('userAnswered', [$this->choiceValue($q1, 1)]);
        $replay = clone $component->instance();
        $component->call('nextQuestion');
        $replay->userAnswered = [$this->choiceValue($q1, 0)];
        $replay->nextQuestion();

        $this->assertSame(1, Quiz::count());
        $this->assertEquals('0', Quiz::first()->is_correct);
        $this->assertEquals($q2->id, $replay->currentQuestion->id);
    }

    public function test_replayed_last_submission_completes_the_quiz()
    {
        $section = $this->makeSection();
        $q = $this->makeQuestion($section, 1, [['정답', true], ['오답', false]]);

        $component = $this->start($section)->set('userAnswered', [$this->choiceValue($q, 0)]);
        $replay = clone $component->instance();
        $component->call('nextQuestion');
        // 답안 저장 후 결과 처리(showResults)에서 실패한 상태
        QuizHeader::query()->update(['completed' => false, 'score' => 0]);
        $replay->nextQuestion();

        $this->assertSame(1, Quiz::count());
        $this->assertTrue($replay->showResult);
        $header = QuizHeader::first();
        $this->assertEquals(1, $header->completed);
        $this->assertEquals(100, $header->score);
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
        $section = $this->makeSection();
        $this->makeQuestion($section, 4, [['Apple', true]]);

        $this->start($section)
            ->set('userAnswered', 'apple')
            ->call('nextQuestion');

        $this->assertEquals('1', Quiz::first()->is_correct);
    }

    public function test_inactive_questions_are_not_asked()
    {
        $section = $this->makeSection();
        $this->makeQuestion($section, 2, [['숨김', true]], ['is_active' => '0']);
        $active = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);

        $this->start($section)
            ->assertSet('quizSize', 1)
            ->assertSet('currentQuestion.id', $active->id)
            ->set('userAnswered', '나는 소년이다')
            ->call('nextQuestion')
            ->assertSet('showResult', true);

        $this->assertEquals([$active->id], Quiz::pluck('question_id')->all());
        $this->assertEquals(100, QuizHeader::first()->score);
    }
}
