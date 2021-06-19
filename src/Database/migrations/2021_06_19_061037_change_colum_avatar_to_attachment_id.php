<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumAvatarToAttachmentId extends Migration
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
            $table->renameColumn('avatar', 'attachment_id');
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
