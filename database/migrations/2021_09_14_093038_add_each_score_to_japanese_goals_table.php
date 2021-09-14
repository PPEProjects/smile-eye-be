<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEachScoreToJapaneseGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('japanese_goals', function (Blueprint $table) {
            $table->float("each_score")->nullable()->after('more');
            $table->renameColumn('score', 'total_score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('japanese_goals', function (Blueprint $table) {
            $table->dropColumn('each_score');
            $table->renameColumn('total_score', 'score');
        });
    }
}
