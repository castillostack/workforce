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
        Schema::create('metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->date('metric_date');
            $table->integer('scheduled_minutes')->nullable();
            $table->integer('worked_minutes')->nullable();
            $table->decimal('adherence_pct', 5, 2)->nullable();
            $table->integer('total_calls')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->unique(['employee_id', 'metric_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics_daily');
    }
};
