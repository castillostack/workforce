<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AdherenceService;
use App\Models\MetricsDaily;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportingController extends Controller
{
    private AdherenceService $adherenceService;

    public function __construct(AdherenceService $adherenceService)
    {
        $this->adherenceService = $adherenceService;
    }

    /**
     * Genera reporte de adherencia para un empleado.
     *
     * @param Request $request
     * @param int $employeeId
     * @return JsonResponse
     */
    public function adherenceReport(Request $request, int $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $metrics = $this->adherenceService->calculateDailyAdherence($employeeId, $validated['date']);

        return response()->json($metrics);
    }

    /**
     * Obtiene métricas diarias.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dailyMetrics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $metric = MetricsDaily::where('employee_id', $validated['employee_id'])
            ->where('metric_date', $validated['date'])
            ->first();

        return response()->json($metric);
    }

    // Métodos para reportes consolidados, AHT, etc.
}