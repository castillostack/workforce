<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'username',
        'employee_code',
        'team_id',
        'supervisor_id',
        'position',
        'extension',
        'hire_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function metricsDaily()
    {
        return $this->hasMany(MetricsDaily::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function requestApprovals()
    {
        return $this->hasMany(RequestApproval::class, 'approver_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function imports()
    {
        return $this->hasMany(Import::class, 'uploaded_by');
    }

    // Relaciones con tablas raw
    public function ahtCallsRaw()
    {
        return $this->hasMany(AhtCallsRaw::class, 'username', 'username');
    }

    public function callsRaw()
    {
        return $this->hasMany(CallsRaw::class, 'username', 'username');
    }

    public function agentStateNotreadyRaw()
    {
        return $this->hasMany(AgentStateNotreadyRaw::class, 'username', 'username');
    }

    public function chatConversationsRaw()
    {
        return $this->hasMany(ChatConversationsRaw::class, 'username', 'username');
    }
}