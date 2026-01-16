<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'status_id');
    }
}