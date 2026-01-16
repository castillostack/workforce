<?php

namespace App\Services\Analytics;

use App\Models\Schedule;
use App\Models\AgentStateNotreadyRaw;
use App\Models\MetricsDaily;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdherenceService
{
    /**
     * Calcula métricas de adherencia diaria para un empleado.
     *
     * @param int $employeeId
     * @param string $date Fecha en formato Y-m-d
     * @return array
     */
    public function calculateDailyAdherence(int $employeeId, string $date): array
    {
        $schedule = Schedule::where('employee_id', $employeeId)
            ->where('schedule_date', $date)
            ->with('shiftTemplate')
            ->first();

        if (!$schedule) {
            Log::warning("No schedule found for employee $employeeId on $date");
            return [];
        }

        // Obtener logs de CISCO para el día
        $logs = AgentStateNotreadyRaw::where('username', $schedule->employee->username)
            ->whereDate('transition_time', $date)
            ->orderBy('transition_time')
            ->get();

        // Calcular métricas
        $metrics = $this->computeMetrics($schedule, $logs);

        // Guardar en MetricsDaily
        MetricsDaily::updateOrCreate(
            ['employee_id' => $employeeId, 'metric_date' => $date],
            $metrics
        );

        return $metrics;
    }

    /**
     * Computa las métricas de adherencia.
     *
     * @param Schedule $schedule
     * @param Collection $logs
     * @return array
     */
    private function computeMetrics(Schedule $schedule, $logs): array
    {
        $scheduledStart = Carbon::parse($schedule->scheduled_start);
        $scheduledEnd = Carbon::parse($schedule->scheduled_end);

        // Encontrar entrada real (primer 'Ready' después de scheduled_start)
        $actualEntry = $this->findActualEntry($logs, $scheduledStart);

        // Calcular tardanza
        $latenessMinutes = $actualEntry ? $scheduledStart->diffInMinutes($actualEntry, false) : null;

        // Adherencia de almuerzo (asumir actividad 'LUNCH')
        $lunchActivity = $schedule->activities()->where('activity_type', 'LUNCH')->first();
        $lunchMetrics = $this->calculateLunchAdherence($lunchActivity, $logs);

        // Adherencia de descansos
        $breakMetrics = $this->calculateBreakAdherence($schedule, $logs);

        // Productividad general
        $totalLoggedSeconds = $this->calculateTotalLoggedTime($logs);
        $totalNotReadySeconds = $this->calculateTotalNotReadyTime($logs);
        $occupancyRate = $totalLoggedSeconds > 0 ? (($totalLoggedSeconds - $totalNotReadySeconds) / $totalLoggedSeconds) * 100 : 0;

        return [
            'scheduled_minutes' => $scheduledStart->diffInMinutes($scheduledEnd),
            'worked_minutes' => $totalLoggedSeconds / 60,
            'adherence_pct' => $this->calculateOverallAdherence($latenessMinutes, $lunchMetrics, $breakMetrics),
            'total_calls' => 0, // Placeholder, calcular desde AHT
            'calculated_at' => now(),
        ];
    }

    /**
     * Encuentra la hora de entrada real.
     *
     * @param Collection $logs
     * @param Carbon $scheduledStart
     * @return Carbon|null
     */
    private function findActualEntry($logs, Carbon $scheduledStart): ?Carbon
    {
        foreach ($logs as $log) {
            if ($log->agent_state === 'Ready' && Carbon::parse($log->transition_time)->gte($scheduledStart)) {
                return Carbon::parse($log->transition_time);
            }
        }
        return null;
    }

    /**
     * Calcula adherencia de almuerzo.
     *
     * @param mixed $lunchActivity
     * @param Collection $logs
     * @return array
     */
    private function calculateLunchAdherence($lunchActivity, $logs): array
    {
        if (!$lunchActivity) return ['duration' => 0, 'deviation' => 0];

        // Lógica simplificada
        return ['duration' => 45, 'deviation' => 0]; // Placeholder
    }

    /**
     * Calcula adherencia de descansos.
     *
     * @param Schedule $schedule
     * @param Collection $logs
     * @return array
     */
    private function calculateBreakAdherence(Schedule $schedule, $logs): array
    {
        // Lógica simplificada
        return ['duration' => 15, 'deviation' => 0]; // Placeholder
    }

    /**
     * Calcula tiempo total logueado.
     *
     * @param Collection $logs
     * @return int Segundos
     */
    private function calculateTotalLoggedTime($logs): int
    {
        // Suma de duraciones donde state != 'Logout'
        return $logs->where('agent_state', '!=', 'Logout')->sum('duration');
    }

    /**
     * Calcula tiempo total Not Ready.
     *
     * @param Collection $logs
     * @return int Segundos
     */
    private function calculateTotalNotReadyTime($logs): int
    {
        return $logs->where('agent_state', 'Not Ready')->sum('duration');
    }

    /**
     * Calcula adherencia general.
     *
     * @param int|null $lateness
     * @param array $lunch
     * @param array $break
     * @return float
     */
    private function calculateOverallAdherence(?int $lateness, array $lunch, array $break): float
    {
        // Lógica simplificada: si tardanza < 5 min, lunch deviation < 5, etc.
        $score = 100;
        if ($lateness && $lateness > 5) $score -= 10;
        if ($lunch['deviation'] > 5) $score -= 10;
        if ($break['deviation'] > 5) $score -= 10;
        return max(0, $score);
    }
}