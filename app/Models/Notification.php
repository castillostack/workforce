<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_id',
        'message',
        'type',
        'status',
    ];

    public function recipient()
    {
        return $this->belongsTo(Employee::class, 'recipient_id');
    }
}