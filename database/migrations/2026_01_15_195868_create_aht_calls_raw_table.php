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
        Schema::create('aht_calls_raw', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_del_agente', 150)->nullable();
            $table->string('username', 50)->nullable();
            $table->string('agent_ext', 20)->nullable();
            $table->timestamp('hora_de_inicio_de_llamada')->nullable();
            $table->timestamp('hora_de_fin_de_llamada')->nullable();
            $table->integer('duracion_de_llamada')->nullable();
            $table->string('numero_llamado', 50)->nullable();
            $table->string('ani_de_llamada', 50)->nullable();
            $table->string('llamada_dirigida_por_csq', 100)->nullable();
            $table->string('other_csq', 100)->nullable();
            $table->string('call_skill', 100)->nullable();
            $table->integer('tiempo_de_conversacion')->nullable();
            $table->integer('tiempo_en_espera')->nullable();
            $table->integer('tiempo_de_cierre')->nullable();
            $table->string('call_type', 50)->nullable();
            $table->timestamp('created_at')->default(DB::raw('NOW()'));
            $table->index('username');
            $table->index('hora_de_inicio_de_llamada');
            $table->index('llamada_dirigida_por_csq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aht_calls_raw');
    }
};
