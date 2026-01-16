<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricsDaily extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'metric_date',
        'scheduled_minutes',
        'worked_minutes',
        'adherence_pct',
        'total_calls',
        'calculated_at',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'adherence_pct' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}