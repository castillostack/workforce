<?php

namespace App\Services\Analytics;

use App\Models\AhtCallsRaw;
use App\Models\CallsRaw;
use App\Models\ChatConversationsRaw;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductivityService
{
    /**
     * Calcula métricas AHT para un empleado en una fecha.
     *
     * @param int $employeeId
     * @param string $date
     * @return array
     */
    public function calculateAhtMetrics(int $employeeId, string $date): array
    {
        $employee = \App\Models\Employee::findOrFail($employeeId);

        // Voz: desde AhtCallsRaw
        $voiceCalls = AhtCallsRaw::where('username', $employee->username)
            ->whereDate('hora_de_inicio_de_llamada', $date)
            ->get();

        $callsHandled = $voiceCalls->count();
        $avgHandleTimeVoice = $callsHandled > 0 ? $voiceCalls->avg('tiempo_de_conversacion') / 60 : 0; // minutos
        $avgTalkTime = $callsHandled > 0 ? $voiceCalls->avg('tiempo_de_conversacion') / 60 : 0;
        $avgHoldTime = $callsHandled > 0 ? $voiceCalls->avg('tiempo_en_espera') / 60 : 0;

        // Chat: desde ChatConversationsRaw
        $chats = ChatConversationsRaw::where('agent_id', $employee->username)
            ->whereDate('conversation_start_time', $date)
            ->get();

        $chatsHandled = $chats->count();
        $avgHandleTimeChat = $chatsHandled > 0 ? $chats->avg('talk_time') / 60 : 0;

        return [
            'calls_handled' => $callsHandled,
            'avg_handle_time_voice' => round($avgHandleTimeVoice, 2),
            'avg_talk_time' => round($avgTalkTime, 2),
            'avg_hold_time' => round($avgHoldTime, 2),
            'chats_handled' => $chatsHandled,
            'avg_handle_time_chat' => round($avgHandleTimeChat, 2),
        ];
    }

    /**
     * Calcula métricas por cola (CSQ).
     *
     * @param string $csqName
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateCsqMetrics(string $csqName, string $startDate, string $endDate): array
    {
        $calls = AhtCallsRaw::where('llamada_dirigida_por_csq', $csqName)
            ->whereBetween('hora_de_inicio_de_llamada', [$startDate, $endDate])
            ->get();

        $totalCalls = $calls->count();
        $avgAht = $totalCalls > 0 ? $calls->avg('tiempo_de_conversacion') / 60 : 0;

        return [
            'csq_name' => $csqName,
            'total_calls' => $totalCalls,
            'avg_aht' => round($avgAht, 2),
        ];
    }
}