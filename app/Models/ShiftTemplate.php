<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'total_minutes',
        'is_overnight',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function shiftBreaks()
    {
        return $this->hasMany(ShiftBreak::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}