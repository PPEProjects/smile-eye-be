<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('goal_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('todolist_id')->nullable();
            $table->string('address')->nullable();
            $table->string('zalo')->nullable();
            $table->string('repeat')->nullable();
            $table->string('reminder')->nullable();
            $table->date('action_at')->nullable();
            $table->time('action_at_time')->nullable();
            $table->text('note')->nullable();
            $table->json('attachment_ids')->nullable();
            $table->json('images')->nullable();
            $table->string('publish')->nullable();
            $table->json('contest')->nullable();
            $table->string('color')->nullable();
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
        Schema::dropIfExists('general_infos');
    }
}
