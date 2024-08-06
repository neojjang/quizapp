<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncorrectAnswerNotesTable extends Migration
{
    /**
     * Run the migrations.
     * 학생의 오답노트 테이블
     * @return void
     */
    public function up()
    {
        Schema::create('incorrect_answer_notes', function (Blueprint $table) {
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
        Schema::dropIfExists('incorrect_answer_notes');
    }
}
