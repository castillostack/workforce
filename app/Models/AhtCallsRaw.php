<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhtCallsRaw extends Model
{
    use HasFactory;

    protected $table = 'aht_calls_raw';

    protected $fillable = [
        'username',
        'nombre_del_agente',
        'id_de_conexion_del_agente',
        'agent_ext',
        'hora_de_inicio_de_llamada',
        'hora_de_fin_de_llamada',
        'duracion_de_llamada',
        'numero_llamado',
        'ani_de_llamada',
        'llamada_dirigida_por_csq',
        'other_csq',
        'call_skill',
        'tiempo_de_conversacion',
        'tiempo_en_espera',
        'tiempo_de_cierre',
        'call_type',
    ];

    protected $casts = [
        'hora_de_inicio_de_llamada' => 'datetime',
        'hora_de_fin_de_llamada' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'username', 'username');
    }
}