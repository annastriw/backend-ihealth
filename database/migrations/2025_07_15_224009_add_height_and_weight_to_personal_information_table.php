<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('personal_information', function (Blueprint $table) {
        $table->string('height')->after('bmi');
        $table->string('weight')->after('height');
    });
}

public function down()
{
    Schema::table('personal_information', function (Blueprint $table) {
        $table->dropColumn(['height', 'weight']);
    });
}

};
