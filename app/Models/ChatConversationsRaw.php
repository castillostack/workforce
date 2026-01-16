<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversationsRaw extends Model
{
    use HasFactory;

    protected $table = 'chat_conversations_raw';

    protected $fillable = [
        'username',
        'agent_name',
        'agent_id',
        'conversation_start_time',
        'conversation_end_time',
        'conversation_duration',
        'conversation_author_id',
        'conversation_destination',
        'chat_conversation',
        'talk_time',
        'acceptance_time',
        'conversation_type',
        'chat_source',
        'chat_rating',
    ];

    protected $casts = [
        'conversation_start_time' => 'datetime',
        'conversation_end_time' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'username', 'username');
    }
}