<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsernamePhoneNumberMediaIdsUsersTable extends Migration
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
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('username')->nullable();
            $table->string('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('phone_number');
        });
    }
}
