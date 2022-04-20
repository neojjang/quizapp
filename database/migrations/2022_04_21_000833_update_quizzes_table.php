<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('quizzes', function ($table) {
        //     // 현재 laravel 에서 enum 타입의 변경은 지원하지 않음 
        //     // https://github.com/doctrine/dbal/issues/3161
        //     DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        //     $table->enum('is_correct', [0, 1, 2])->change();
        // });
        if (Schema::hasColumn('quizzes', 'is_correct')) {
            Schema::table('quizzes', function ($table) {
                $table->dropColumn('is_correct');
            });
        }
        Schema::table('quizzes', function ($table) {
            $table->enum('is_correct', [0, 1, 2])->default(0)->comment('0:틀림,1:정답,2:보류')->after('answer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('quizzes', 'is_correct')) {
            Schema::table('quizzes', function ($table) {
                $table->dropColumn('is_correct');
            });
        }

        Schema::table('quizzes', function ($table) {
            //
            $table->enum('is_correct', [0, 1])->default(0)->after('answer_id');
        });
    }
}
