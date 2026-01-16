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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('username')->unique();
            $table->string('employee_code')->unique();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('supervisor_id')->nullable()->constrained('employees');
            $table->string('position')->nullable();
            $table->string('extension')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
