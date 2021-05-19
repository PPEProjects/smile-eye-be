<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAvatarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    protected $schema;

    public function __construct()
    {
        $this->schema = Schema::connection(config('ppe.core_db_connections'));
    }
    public function up()
    {
        $this->schema->dropIfExists('user_avatars');
        $this->schema->create('user_avatars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('background_file')->nullable();
            $table->json('face')->nullable();
            $table->json('hair')->nullable();
            $table->json('eyebrows')->nullable();
            $table->json('body')->nullable();
            $table->json('eyes')->nullable();
            $table->json('mouth')->nullable();
            $table->json('nose')->nullable();
            $table->json('shirt')->nullable();
            $table->json('trouser')->nullable();
            $table->json('shoes')->nullable();
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
        $this->schema->dropIfExists('user_avatars');
    }
}
