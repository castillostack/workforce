<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'schedule_date',
        'shift_template_id',
        'status_id',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftTemplate()
    {
        return $this->belongsTo(ShiftTemplate::class);
    }

    public function status()
    {
        return $this->belongsTo(ScheduleStatus::class, 'status_id');
    }

    public function scheduleVersions()
    {
        return $this->hasMany(ScheduleVersion::class);
    }
}