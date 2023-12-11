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
            $table->index([DB::raw('question(64)')]);
        });
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
