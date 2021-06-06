<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuotesToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection(config('ppe.core_db_connections'));
    }
    public function up()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->string('quotes')->nullable()->after('password');
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
                $table->dropColumn('quotes');
        });
    }
}
