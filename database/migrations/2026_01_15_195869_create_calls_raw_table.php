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
        Schema::create('calls_raw', function (Blueprint $table) {
            $table->id();
            $table->string('id_de_sesion', 30)->nullable();
            $table->integer('numero_de_secuencia')->nullable();
            $table->timestamp('hora_de_inicio')->nullable();
            $table->timestamp('hora_de_fin')->nullable();
            $table->integer('disposicion_de_contacto')->nullable();
            $table->string('csq_name', 120)->nullable();
            $table->string('nombre_del_agente', 150)->nullable();
            $table->string('numero_del_autor', 50)->nullable();
            $table->string('numero_de_destino', 50)->nullable();
            $table->string('numero_llamado', 50)->nullable();
            $table->integer('tiempo_de_conversacion')->nullable();
            $table->integer('tiempo_de_timbre')->nullable();
            $table->integer('tiempo_de_trabajo')->nullable();
            $table->integer('tiempo_en_cola')->nullable();
            $table->timestamp('created_at')->default(DB::raw('NOW()'));
            $table->index('id_de_sesion');
            $table->index('hora_de_inicio');
            $table->index('csq_name');
            $table->index('nombre_del_agente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls_raw');
    }
};
