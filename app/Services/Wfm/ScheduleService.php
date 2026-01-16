<?php

namespace App\Services\Wfm;

use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    /**
     * Crea o actualiza un horario para un empleado.
     *
     * @param int $employeeId
     * @param array $data
     * @return Schedule
     */
    public function createOrUpdateSchedule(int $employeeId, array $data): Schedule
    {
        DB::beginTransaction();

        try {
            $schedule = Schedule::updateOrCreate(
                ['employee_id' => $employeeId, 'schedule_date' => $data['schedule_date']],
                [
                    'shift_template_id' => $data['shift_template_id'] ?? null,
                    'status_id' => $data['status_id'] ?? 1, // Default active
                    'notes' => $data['notes'] ?? null,
                ]
            );

            DB::commit();
            return $schedule;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando schedule: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene horarios para un rango de fechas.
     *
     * @param int $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSchedulesInRange(int $employeeId, string $startDate, string $endDate)
    {
        return Schedule::where('employee_id', $employeeId)
            ->whereBetween('schedule_date', [$startDate, $endDate])
            ->with('shiftTemplate')
            ->orderBy('schedule_date')
            ->get();
    }

    /**
     * Valida si un horario es válido (no solapamientos, etc.).
     *
     * @param array $data
     * @return bool
     */
    public function validateSchedule(array $data): bool
    {
        // Lógica de validación: ej. no solapar con otros schedules
        // Placeholder
        return true;
    }
}