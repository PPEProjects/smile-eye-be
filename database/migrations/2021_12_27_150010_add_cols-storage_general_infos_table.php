<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsStorageGeneralInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('general_infos', function (Blueprint $table) {
            $table->json('storage')->nullable()->after('attachment_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('general_infos', function (Blueprint $table) {
            $table->dropColumn('storage');
        });
    }
}
