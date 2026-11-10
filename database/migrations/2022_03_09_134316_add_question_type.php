<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuestionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            // "선택형", "번역:서술형(첫번째 답만 사용)", "영작:서술형(첫번째 답만 사용)", "영작:구문나열형(선택)", "영작:구문나열형"
            $table->unsignedTinyInteger('type_id')->default(1)->after('explanation')->comment('문제유형. 1:선택형,2:주관식,3:주관식,4:구문나열형(선택),5:구문나열형');
            // $table->index('question')
        });
        // 접두사 인덱스는 MySQL/MariaDB 전용 (개발·테스트 SQLite에서는 생략).
        // Laravel 10부터 index()가 DB::raw를 받지 않아 SQL로 만든다. 이름은 Laravel 8이 만들던 것과 같게 둔다
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('CREATE INDEX `questions_question(64)_index` ON questions (question(64))');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            //
            $table->dropColumn('type_id');
        });
    }
}
