<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'source_type',
        'status',
        'uploaded_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(Employee::class, 'uploaded_by');
    }
}