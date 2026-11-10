<?php

namespace Tests\Feature\Quiz;

use App\Constants\Section as SectionType;
use App\Http\Livewire\UserQuizlv;
use App\Models\Quiz;
use App\Models\QuizHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** OMR 답안지형·듣기평가형: 모든 답을 한 번에 제출(checkAllAnswers)하며 자동 채점 */
class AnswerSheetQuizTest extends TestCase
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

    public static function sectionTypes(): array
    {
        return [
            'OMR' => [SectionType::OMR],
            '듣기평가' => [SectionType::LISTENING_TEST],
        ];
    }

    #[DataProvider('sectionTypes')]
    public function test_all_answers_are_graded_together($sectionType)
    {
        $section = $this->makeSection($sectionType);
        $choice = $this->makeQuestion($section, 1, [['1번', false], ['2번', true], ['3번', false]]);
        $short = $this->makeQuestion($section, 4, [['Apple', true]]);
        $writing = $this->makeQuestion($section, 3, [['I am a boy.', true]]);

        $this->start($section)
            ->assertSet('isDisabled', true)
            ->set('omrAnswered', [
                0 => [1 => $this->choiceValue($choice, 1)],
                1 => [0 => ' apple '],
                2 => [0 => 'I am a girl.'],
            ])
            ->assertSet('isDisabled', false)
            ->call('checkAllAnswers')
            ->assertSet('showResult', true);

        $results = Quiz::orderBy('question_id')->pluck('is_correct', 'question_id');
        $this->assertEquals('1', $results[$choice->id]);
        $this->assertEquals('1', $results[$short->id]);
        $this->assertEquals('0', $results[$writing->id]);

        $header = QuizHeader::first();
        $this->assertEquals(66.67, $header->score);
        $this->assertEquals(1, $header->completed);
        $this->assertEquals([$choice->id, $short->id, $writing->id], unserialize($header->questions_taken));
    }

    public function test_choosing_a_wrong_option_alongside_the_right_one_is_wrong()
    {
        $section = $this->makeSection(SectionType::OMR);
        $choice = $this->makeQuestion($section, 1, [['1번', false], ['2번', true]]);

        $this->start($section)
            ->set('omrAnswered', [0 => [0 => $this->choiceValue($choice, 0), 1 => $this->choiceValue($choice, 1)]])
            ->call('checkAllAnswers');

        $this->assertEquals('0', Quiz::first()->is_correct);
        $this->assertEquals(0, QuizHeader::first()->score);
    }

    public function test_submit_stays_disabled_until_every_question_is_answered()
    {
        $section = $this->makeSection(SectionType::OMR);
        $choice = $this->makeQuestion($section, 1, [['1번', true], ['2번', false]]);
        $this->makeQuestion($section, 4, [['Apple', true]]);

        $this->start($section)
            ->set('omrAnswered', [0 => [0 => $this->choiceValue($choice, 0)]])
            ->assertSet('isDisabled', true);
    }

    public function test_submitting_twice_does_not_duplicate_records()
    {
        $section = $this->makeSection(SectionType::OMR);
        $choice = $this->makeQuestion($section, 1, [['1번', true], ['2번', false]]);

        $this->start($section)
            ->set('omrAnswered', [0 => [0 => $this->choiceValue($choice, 0)]])
            ->call('checkAllAnswers')
            ->call('checkAllAnswers');

        $this->assertSame(1, Quiz::count());
        $this->assertEquals(100, QuizHeader::first()->score);
    }

    public function test_replayed_submission_is_not_saved_again()
    {
        $section = $this->makeSection(SectionType::OMR);
        $this->makeQuestion($section, 1, [['1번', true], ['2번', false]]);

        $component = $this->start($section)->set('omrAnswered.0.1', true);
        $component->instance()->checkAllAnswers();
        // 제출 전 상태를 다시 보낸 경우
        $component->instance()->quizInProgress = true;
        $component->instance()->omrAnswered = [0 => [0 => true]];
        $component->instance()->checkAllAnswers();

        $this->assertSame(1, Quiz::count());
        $this->assertEquals(0, QuizHeader::first()->score);
    }

    public function test_failure_while_grading_saves_nothing()
    {
        $section = $this->makeSection(SectionType::OMR);
        $this->makeQuestion($section, 4, [['Apple', true]]);
        $this->makeQuestion($section, 2, [['나는 학교에 가는 소년이다', true]]);
        Process::fake(['*' => fn () => throw new \RuntimeException('checker crashed')]);

        $component = $this->start($section)->set('omrAnswered', [0 => [0 => 'apple'], 1 => [0 => '나는 학교에 걸어가는 소년입니다']]);
        try {
            $component->instance()->checkAllAnswers();
            $this->fail('예외가 발생해야 한다');
        } catch (\RuntimeException) {
        }

        $this->assertSame(0, Quiz::count());
    }

    public function test_out_of_range_checkbox_index_is_ignored()
    {
        $section = $this->makeSection(SectionType::OMR);
        $this->makeQuestion($section, 1, [['1번', true], ['2번', false]]);

        $this->start($section)
            ->set('omrAnswered', [0 => [0 => true, 9 => true]])
            ->call('checkAllAnswers')
            ->assertSet('showResult', true);

        $this->assertEquals('1', Quiz::first()->is_correct);
    }

    public function test_inactive_questions_are_not_shown_or_graded()
    {
        $section = $this->makeSection(SectionType::OMR);
        $this->makeQuestion($section, 1, [['1번', true], ['2번', false]], ['is_active' => '0']);
        $active = $this->makeQuestion($section, 4, [['Apple', true]]);

        $this->start($section)
            ->assertCount('questions', 1)
            ->set('omrAnswered', [0 => [0 => 'apple']])
            ->assertSet('isDisabled', false)
            ->call('checkAllAnswers');

        $this->assertEquals([$active->id], Quiz::pluck('question_id')->all());
        $this->assertEquals(100, QuizHeader::first()->score);
    }

    /** 브라우저(Livewire 3)는 체크한 보기를 omrAnswered[문항][보기순번] = true 로 보낸다 */
    #[DataProvider('sectionTypes')]
    public function test_checkbox_values_sent_by_livewire_3_are_graded($sectionType)
    {
        $section = $this->makeSection($sectionType);
        $right = $this->makeQuestion($section, 1, [['1번', false], ['2번', true], ['3번', false]]);
        $wrong = $this->makeQuestion($section, 1, [['1번', true], ['2번', false]]);

        $this->start($section)
            ->set('omrAnswered.0.1', true)
            ->set('omrAnswered.1.1', true)
            ->assertSet('isDisabled', false)
            ->call('checkAllAnswers')
            ->assertSet('showResult', true);

        $this->assertEquals('1', Quiz::where('question_id', $right->id)->value('is_correct'));
        $this->assertEquals('0', Quiz::where('question_id', $wrong->id)->value('is_correct'));
        $this->assertEquals($right->answers[1]->id, Quiz::where('question_id', $right->id)->value('user_answer'));
        $this->assertEquals(50, QuizHeader::first()->score);
    }
}
