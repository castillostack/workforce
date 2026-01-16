<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_type_id',
        'department_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class, 'workflow_id');
    }
}