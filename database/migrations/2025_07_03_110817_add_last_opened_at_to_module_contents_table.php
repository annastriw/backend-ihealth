<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_contents', function (Blueprint $table) {
            $table->dateTime('last_opened_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void {
        Schema::table('module_contents', function (Blueprint $table) {
            $table->dropColumn('last_opened_at');
        });
    }
};
