<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediumGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medium_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('중분류 이름');
            $table->string('description')->comment('중분류 설명');
            $table->enum('is_active', ['0', '1'])->default('1')->comment('활성/비활성');
            $table->text('details')->comment('상세설명');
            $table->unsignedBigInteger('major_group_id')->nullable()->comment('대분류 id');
            $table->foreign('major_group_id')->references('id')->on('major_groups')->onDelete('cascade');
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
        Schema::dropIfExists('medium_groups');
    }
}
