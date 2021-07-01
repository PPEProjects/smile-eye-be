<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublishInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publish_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('general_id')->nullable();
            $table->unsignedBigInteger('user_invite_id')->nullable();
            $table->string('status')->nullable();
            $table->string('rule')->nullable();
            $table->boolean('is_copy')->nullable();
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
        Schema::dropIfExists('publish_infos');
    }
}
