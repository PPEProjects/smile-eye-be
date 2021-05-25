<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdUpdateAtPasswordReset extends Migration
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
        $this->schema->table('password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('password_resets');
    }
}
