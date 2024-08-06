<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswerNoteTestPapersTable extends Migration
{
    /**
     * Run the migrations.
     * 오답노트 문제지 관리 테이블
     * @return void
     */
    public function up()
    {
        Schema::create('answer_note_test_papers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('answer_note_test_papers');
    }
}
