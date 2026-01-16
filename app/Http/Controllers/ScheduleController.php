<?php

namespace App\Http\Controllers;

use App\Services\Wfm\WorkflowService;
use App\Services\Wfm\ScheduleService;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    private WorkflowService $workflowService;
    private ScheduleService $scheduleService;

    public function __construct(WorkflowService $workflowService, ScheduleService $scheduleService)
    {
        $this->workflowService = $workflowService;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Obtiene el horario de un empleado.
     *
     * @param Request $request
     * @param int $employeeId
     * @return JsonResponse
     */
    public function getSchedule(Request $request, int $employeeId): JsonResponse
    {
        $schedules = Schedule::where('employee_id', $employeeId)
            ->with('shiftTemplate')
            ->get();

        return response()->json($schedules);
    }

    /**
     * Inicia una solicitud de intercambio de turno.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requestSwap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_user_id' => 'required|integer',
            'original_date' => 'required|date',
            'swap_date' => 'nullable|date',
        ]);

        $requestObj = $this->workflowService->initiateShiftSwap(
            $request->user()->id,
            $validated['target_user_id'],
            $validated
        );

        return response()->json($requestObj, 201);
    }

    /**
     * Decide sobre una solicitud de swap (peer).
     *
     * @param Request $request
     * @param int $requestId
     * @return JsonResponse
     */
    public function decideSwap(Request $request, int $requestId): JsonResponse
    {
        $validated = $request->validate([
            'approved' => 'required|boolean',
        ]);

        $approved = $this->workflowService->peerDecision(
            $requestId,
            $request->user()->id,
            $validated['approved']
        );

        return response()->json(['approved' => $approved]);
    }

    /**
     * Crea o actualiza un horario.
     *
     * @param Request $request
     * @param int $employeeId
     * @return JsonResponse
     */
    public function createSchedule(Request $request, int $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'schedule_date' => 'required|date',
            'shift_template_id' => 'nullable|integer',
            'status_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $schedule = $this->scheduleService->createOrUpdateSchedule($employeeId, $validated);

        return response()->json($schedule, 201);
    }
}