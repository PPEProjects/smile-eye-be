<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goal_members', function (Blueprint $table) {
            $table->id();
            $table->integer('add_user_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('goal_id')->nullable();
            $table->integer('teacher_id')->nullable();
            $table->integer('rank')->nullable();
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
        Schema::dropIfExists('goal_members');
    }
}
