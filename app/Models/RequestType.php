<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'requires_hr',
        'max_days',
    ];

    protected $casts = [
        'requires_hr' => 'boolean',
    ];

    public function approvalWorkflows()
    {
        return $this->hasMany(ApprovalWorkflow::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}