<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aht_calls_raw', function (Blueprint $table) {
            $table->foreign('username')->references('username')->on('employees');
        });
    }

    public function down(): void
    {
        Schema::table('aht_calls_raw', function (Blueprint $table) {
            $table->dropForeign(['username']);
        });
    }
};
