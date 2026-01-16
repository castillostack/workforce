<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_conversations_raw', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name', 150)->nullable();
            $table->string('username', 50)->nullable();
            $table->timestamp('conversation_start_time')->nullable();
            $table->timestamp('conversation_end_time')->nullable();
            $table->integer('conversation_duration')->nullable();
            $table->string('conversation_author_id', 100)->nullable();
            $table->string('conversation_destination', 100)->nullable();
            $table->string('chat_conversation', 100)->nullable();
            $table->integer('talk_time')->nullable();
            $table->integer('acceptance_time')->nullable();
            $table->string('conversation_type', 50)->nullable();
            $table->string('chat_source', 100)->nullable();
            $table->string('chat_rating', 20)->nullable();
            $table->timestamp('created_at')->default(DB::raw('NOW()'));
            $table->index('username');
            $table->index('conversation_start_time');
            $table->index('conversation_type');
            $table->index('chat_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations_raw');
    }
};
