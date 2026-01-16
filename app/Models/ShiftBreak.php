<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_template_id',
        'break_type',
        'duration_minutes',
        'offset_minutes',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    public function shiftTemplate()
    {
        return $this->belongsTo(ShiftTemplate::class);
    }
}