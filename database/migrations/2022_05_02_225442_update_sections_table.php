<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sections', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('class_room_id')->nullable()->after('details');
            $table->foreign('class_room_id')->references('id')->on('class_rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sections', 'class_room_id')) {
            Schema::table('sections', function ($table) {
                $table->dropForeign('class_room_id');
                $table->dropIndex('class_room_id');
                $table->dropColumn('class_room_id');
            });
        }

        Schema::table('sections', function ($table) {
            //
        });
    }
}
