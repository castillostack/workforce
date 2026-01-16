<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallsRaw extends Model
{
    use HasFactory;

    protected $table = 'calls_raw';

    protected $fillable = [
        'username',
        'id_de_sesion',
        'numero_de_secuencia',
        'hora_de_inicio',
        'hora_de_fin',
        'disposicion_de_contacto',
        'csq_name',
        'nombre_del_agente',
        'numero_del_autor',
        'numero_de_destino',
        'numero_llamado',
        'tiempo_de_conversacion',
        'tiempo_de_timbre',
        'tiempo_de_trabajo',
        'tiempo_en_cola',
    ];

    protected $casts = [
        'hora_de_inicio' => 'datetime',
        'hora_de_fin' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'username', 'username');
    }
}