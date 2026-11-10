<?php

namespace Tests\Feature\Quiz;

use App\Constants\Section as SectionType;
use App\Http\Livewire\UserQuizlv;
use App\Models\Quiz;
use App\Models\QuizHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;
use Tests\TestCase;

/** 영작나열형: 섞인 구문을 순서대로 선택, 재시도 허용 횟수(retry) 적용 */
class SentenceOrderQuizTest extends TestCase
{
    use RefreshDatabase;
    use BuildsQuizzes;

    private function start(int $retry): TestableLivewire
    {
        $section = $this->makeSection(SectionType::ENGLISH_COMPOSITION_CLICK);
        $this->makeQuestion($section, 5, [['I / am / a boy', true]], ['retry' => $retry]);

        return Livewire::actingAs($this->makeUser())
            ->test(UserQuizlv::class, $this->mountParams($section))
            ->set('sectionId', $section->id)
            ->call('startQuiz');
    }

    /** 화면에서 구문을 클릭하는 것과 같다: 섞인 목록에서 위치를 찾아 checkSentenceOrder 호출 */
    private function choose(TestableLivewire $component, array $phrases): TestableLivewire
    {
        foreach ($phrases as $phrase) {
            $index = collect($component->get('currentExample'))
                ->search(fn ($example) => trim($example[0]) === $phrase && $example[1]);
            $component->call('checkSentenceOrder', $index, $phrase);
        }

        return $component;
    }

    public function test_correct_order_scores_100()
    {
        $component = $this->start(1);

        $this->choose($component, ['I', 'am', 'a boy'])
            ->assertSet('isDisabled', false)
            ->call('nextQuestion')
            ->assertSet('showResult', true);

        $quiz = Quiz::first();
        $this->assertEquals('1', $quiz->is_correct);
        $this->assertEquals('I / am / a boy', $quiz->user_answer);
        $this->assertEquals(1, $quiz->retry);
        $this->assertEquals(100, QuizHeader::first()->score);
    }

    public function test_wrong_order_without_retry_can_move_on_and_scores_0()
    {
        $component = $this->start(1);

        $this->choose($component, ['am', 'I', 'a boy'])
            ->assertSet('showRetry', false)
            ->assertSet('isDisabled', false)
            ->call('nextQuestion');

        $this->assertEquals('0', Quiz::first()->is_correct);
        $this->assertEquals(0, QuizHeader::first()->score);
    }

    public function test_retry_records_attempts_and_final_result()
    {
        $component = $this->start(2);

        $this->choose($component, ['am', 'I', 'a boy'])
            ->assertSet('showRetry', true)
            ->assertSet('isDisabled', true)
            ->call('retryQuestion')
            ->assertSet('userAnswered', []);

        $this->choose($component, ['I', 'am', 'a boy'])
            ->call('nextQuestion');

        $quiz = Quiz::first();
        $this->assertEquals('1', $quiz->is_correct);
        $this->assertEquals(2, $quiz->retry);
        $attempts = json_decode($quiz->extra_info, true);
        $this->assertCount(2, $attempts);
        $this->assertEquals(['am', 'I', 'a boy'], array_column($attempts[0], 0));
    }

    public function test_deselecting_a_phrase_makes_it_selectable_again()
    {
        $component = $this->start(1);

        $this->choose($component, ['am'])
            ->call('deleteSelectedSentence', 0)
            ->assertSet('userAnswered', []);

        $this->choose($component, ['I', 'am', 'a boy'])
            ->call('nextQuestion');

        $this->assertEquals('1', Quiz::first()->is_correct);
    }

    public function test_submitting_twice_does_not_duplicate_the_record()
    {
        // 마지막 문제에서 "다음" 버튼이 두 번 눌린 경우(더블 클릭)
        $this->markTestIncomplete('B3: 중복 기록 생성, 점수 100→50, quiz_size 1→2 후 오류 (docs/upgrade/PLAN.md 8장)');
        $component = $this->start(1);
        $this->choose($component, ['I', 'am', 'a boy'])
            ->call('nextQuestion')
            ->call('nextQuestion');

        $this->assertSame(1, Quiz::count());
        $this->assertEquals(100, QuizHeader::first()->score);
    }
}
