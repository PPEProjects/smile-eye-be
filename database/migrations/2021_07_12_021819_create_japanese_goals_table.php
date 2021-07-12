<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJapaneseGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('japanese_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("goal_id")->nullable();
            $table->string("type")->nullable();
            $table->json("more")->nullable();
            $table->json("attachments_1")->nullable();
            $table->json("attachments_2")->nullable();
            $table->json("attachments_3")->nullable();
            $table->float("score")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('japanese_goal');
    }
}
