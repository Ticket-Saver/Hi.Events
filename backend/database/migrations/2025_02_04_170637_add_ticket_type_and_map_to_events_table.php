<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('tipoticket', ['general', 'enumerado'])->default('general')->after('id');
            $table->enum('map', ['map1', 'map2'])->nullable()->after('tipoticket');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('tipoticket');
            $table->dropColumn('map');
        });
    }
};