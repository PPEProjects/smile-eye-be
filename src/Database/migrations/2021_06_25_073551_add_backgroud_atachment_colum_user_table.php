<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackgroudAtachmentColumUserTable extends Migration
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
        $this->schema->table('users', function(Blueprint $table) {
            $table->renameColumn('attachment_id', 'avatar_attachment_id');
            $table->json('background_attachment_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('attachment_id', function (Blueprint $table) {
        //
//        });
    }
}
