<?php

namespace Tests\Feature\Quiz;

use App\Http\Livewire\UserQuizlv;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** 번역 문제의 Python 유사도 채점(답이 10자를 넘고 정답과 다를 때만 실행) */
class AnswerCheckerTest extends TestCase
{
    use RefreshDatabase;
    use BuildsQuizzes;

    private const ANSWER = '나는 학교에 가는 소년이다';

    private function submit(string $userAnswer)
    {
        $section = $this->makeSection();
        $this->makeQuestion($section, 2, [[self::ANSWER, true]]);

        Livewire::actingAs($this->makeUser())
            ->test(UserQuizlv::class, $this->mountParams($section))
            ->set('sectionId', $section->id)
            ->call('startQuiz')
            ->set('userAnswered', $userAnswer)
            ->call('nextQuestion');

        return Quiz::first();
    }

    public static function scores(): array
    {
        return [
            '정답(0.75 이상)' => ["0.82\n", '1'],
            '보류(0.40 이상)' => ["0.5\n", '2'],
            '오답' => ["0.1\n", '0'],
        ];
    }

    #[DataProvider('scores')]
    public function test_similarity_score_decides_the_result($output, $expected)
    {
        Process::fake(['*' => Process::result($output)]);

        $this->assertEquals($expected, $this->submit('나는 학교에 걸어가는 소년입니다')->is_correct);
    }

    public function test_answers_are_passed_as_arguments_without_a_shell()
    {
        Process::fake(['*' => Process::result('0.1')]);
        $malicious = '나는 소년이다 $(rm -rf ~) `id`';

        $this->submit($malicious);

        Process::assertRan(fn ($process) => $process->command === [
            config('services.answer_checker.python'),
            config('services.answer_checker.script'),
            escapeshellarg(self::ANSWER),
            escapeshellarg($malicious),
        ] && $process->timeout === 30);
    }

    public function test_checker_failure_is_graded_wrong()
    {
        Process::fake(['*' => Process::result(output: '', errorOutput: 'ModuleNotFoundError', exitCode: 1)]);

        $this->assertEquals('0', $this->submit('나는 학교에 걸어가는 소년입니다')->is_correct);
    }

    public function test_checker_timeout_is_graded_wrong()
    {
        // 실제 프로세스를 제한 시간 1초로 실행해 시간 초과 예외를 낸다
        $script = tempnam(sys_get_temp_dir(), 'checker');
        file_put_contents($script, '<?php sleep(5);');
        config(['services.answer_checker' => ['python' => PHP_BINARY, 'script' => $script, 'timeout' => 1]]);

        $this->assertEquals('0', $this->submit('나는 학교에 걸어가는 소년입니다')->is_correct);
        unlink($script);
    }

    public function test_exact_match_skips_the_checker()
    {
        Process::fake();

        $this->assertEquals('1', $this->submit('나는 학교에  가는 소년이다')->is_correct);
        Process::assertNothingRan();
    }
}
