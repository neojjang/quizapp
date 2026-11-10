<?php

namespace Tests\Feature\Quiz;

use App\Constants\Section as SectionType;
use App\Models\Answer;
use App\Models\ClassRoom;
use App\Models\MajorGroup;
use App\Models\MediumGroup;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizHeader;
use App\Models\Quote;
use App\Models\Section;
use App\Models\User;

/**
 * 대분류 → 중분류 → 수업 → 시험 → 문제 → 보기 체인을 만드는 테스트 헬퍼.
 * Question.type_id는 DB 값(1:선택형, 2:번역, 3:영작, 4:단답, 5:구문나열)을 그대로 쓴다.
 */
trait BuildsQuizzes
{
    /** 콘텐츠 작성자(관리자). 콘텐츠 테이블의 user_id 외래키용 */
    protected ?User $owner = null;

    protected function ownerId(): int
    {
        return ($this->owner = $this->owner ?? $this->makeUser('admin'))->id;
    }

    protected function makeUser(string $role = 'user'): User
    {
        return tap(User::factory()->create())->assignRole($role);
    }

    protected function makeClassRoom(): ClassRoom
    {
        $base = ['user_id' => $this->ownerId(), 'description' => 'd', 'details' => 'd', 'is_active' => '1'];
        $major = MajorGroup::forceCreate($base + ['name' => '대분류']);
        $medium = MediumGroup::forceCreate($base + ['name' => '중분류', 'major_group_id' => $major->id]);
        Quote::forceCreate(['author' => 'a', 'quote' => 'q']);

        return ClassRoom::forceCreate($base + ['name' => '수업', 'medium_group_id' => $medium->id]);
    }

    protected function makeSection(int $typeId = SectionType::NORMAL, ?ClassRoom $classRoom = null): Section
    {
        $classRoom = $classRoom ?? $this->makeClassRoom();

        return Section::create([
            'user_id' => $this->ownerId(),
            'class_room_id' => $classRoom->id,
            'type_id' => $typeId,
            'name' => '시험',
            'description' => 'd',
            'details' => 'd',
            'is_active' => '1',
        ]);
    }

    /**
     * @param array $answers [[보기, 정답여부], ...]
     */
    protected function makeQuestion(Section $section, int $typeId, array $answers, array $attributes = []): Question
    {
        $question = Question::create($attributes + [
            'section_id' => $section->id,
            'user_id' => $this->ownerId(),
            'question' => '문제',
            'explanation' => '해설',
            'is_active' => '1',
            'type_id' => $typeId,
            'retry' => 1,
            'timer' => 0,
        ]);
        foreach ($answers as [$text, $isChecked]) {
            Answer::create(['question_id' => $question->id, 'answer' => $text, 'is_checked' => $isChecked ? '1' : '0']);
        }

        return $question->load('answers');
    }

    /** 체크박스 값 형식: "answerId,is_checked" */
    protected function choiceValue(Question $question, int $answerIndex): string
    {
        $answer = $question->answers[$answerIndex];

        return $answer->id . ',' . $answer->is_checked;
    }

    protected function mountParams(Section $section): array
    {
        $classRoom = $section->class_room;
        $medium = MediumGroup::find($classRoom->medium_group_id);

        return [
            'major_group' => MajorGroup::find($medium->major_group_id),
            'medium_group' => $medium,
            'class_room' => $classRoom,
        ];
    }

    /**
     * 응시 완료 기록을 직접 만든다.
     * @param array $results [[Question, is_correct('0'|'1'|'2'), user_answer], ...]
     */
    protected function makeTakenQuiz(User $user, Section $section, array $results): QuizHeader
    {
        $correct = collect($results)->where(1, '1')->count();
        $header = QuizHeader::create([
            'user_id' => $user->id,
            'section_id' => $section->id,
            'quiz_size' => count($results),
            'completed' => true,
            'score' => round($correct / count($results) * 100, 2),
            'questions_taken' => serialize(array_map(fn ($r) => $r[0]->id, $results)),
        ]);
        foreach ($results as [$question, $isCorrect, $userAnswer]) {
            Quiz::create([
                'user_id' => $user->id,
                'quiz_header_id' => $header->id,
                'section_id' => $section->id,
                'question_id' => $question->id,
                'answer_id' => $question->answers[0]->id,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
            ]);
        }

        return $header;
    }
}
