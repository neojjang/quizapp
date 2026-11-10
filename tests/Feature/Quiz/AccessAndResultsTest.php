<?php

namespace Tests\Feature\Quiz;

use App\Constants\Section as SectionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** 권한별 접근과 주요 화면 렌더링 */
class AccessAndResultsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsQuizzes;

    public function test_guest_is_redirected_to_login()
    {
        $this->get(route('userQuizHome'))->assertRedirect(route('login'));
        $this->get(route('adminhome'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_open_admin_pages()
    {
        $this->actingAs($this->makeUser())->get(route('adminhome'))->assertForbidden();
    }

    public function test_admin_home_renders()
    {
        $this->actingAs($this->makeUser('admin'))->get(route('adminhome'))->assertOk();
    }

    public function test_student_home_lists_taken_quizzes()
    {
        $student = $this->makeUser();
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $this->makeTakenQuiz($student, $section, [[$q, '1', '나는 소년이다']]);

        $this->actingAs($student)->get(route('userQuizHome'))->assertOk()->assertSee($section->name);
    }

    public function test_student_can_open_own_quiz_result()
    {
        $student = $this->makeUser();
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $header = $this->makeTakenQuiz($student, $section, [[$q, '0', '나는 소녀이다']]);

        $this->actingAs($student)
            ->get(route('userQuizDetails', $header->id))
            ->assertOk()
            ->assertSee('나는 소녀이다');
    }

    public function test_result_marks_every_chosen_option_of_a_multi_answer_question()
    {
        $student = $this->makeUser();
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 1, [['1번', false], ['2번', true], ['3번', true]]);
        $header = $this->makeTakenQuiz($student, $section, [[$q, '1', $q->answers[1]->id . ',' . $q->answers[2]->id]]);

        $html = $this->actingAs($student)->get(route('userQuizDetails', $header->id))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, '&check;'));
    }

    public function test_result_shows_quiz_duration_in_whole_minutes()
    {
        $student = $this->makeUser();
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $header = $this->makeTakenQuiz($student, $section, [[$q, '1', '나는 소년이다']]);
        $header->timestamps = false;
        $header->forceFill(['created_at' => '2026-11-10 10:00:00', 'updated_at' => '2026-11-10 10:02:30'])->save();

        $html = $this->actingAs($student)->get(route('userQuizDetails', $header->id))->getContent();

        $this->assertMatchesRegularExpression('/\s2 Minutes/', $html);
    }

    public function test_another_students_or_missing_result_is_not_found()
    {
        $section = $this->makeSection(SectionType::OMR);
        $q = $this->makeQuestion($section, 2, [['나는 소년이다', true]]);
        $header = $this->makeTakenQuiz($this->makeUser(), $section, [[$q, '1', '나는 소년이다']]);
        $other = $this->makeUser();

        $this->actingAs($other)->get(route('userQuizDetails', $header->id))->assertNotFound();
        $this->actingAs($other)->get(route('userQuizDetails', 9999))->assertNotFound();
    }

    public function test_quiz_start_page_renders_for_a_class_room()
    {
        $section = $this->makeSection();
        $params = $this->mountParams($section);

        $this->actingAs($this->makeUser())
            ->get(route('startQuizWithClassRoom', [$params['major_group'], $params['medium_group'], $params['class_room']]))
            ->assertOk()
            ->assertSee($section->name);
    }
}
