<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStateNotreadyRaw extends Model
{
    use HasFactory;

    protected $table = 'agent_state_notready_raw';

    protected $fillable = [
        'username',
        'agent_login_id',
        'transition_time',
        'agent_state',
        'reason_code',
        'duration',
    ];

    protected $casts = [
        'transition_time' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'username', 'username');
    }
}