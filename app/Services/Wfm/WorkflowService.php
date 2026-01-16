<?php

namespace App\Services\Wfm;

use App\Models\ShiftSwapRequest;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class WorkflowService
{
    /**
     * Inicia una solicitud de intercambio de turno.
     *
     * @param int $requesterId ID del solicitante.
     * @param int $targetUserId ID del usuario objetivo.
     * @param array $data Datos adicionales (fechas, etc.).
     * @return ShiftSwapRequest
     */
    public function initiateShiftSwap(int $requesterId, int $targetUserId, array $data): ShiftSwapRequest
    {
        DB::beginTransaction();

        try {
            $request = ShiftSwapRequest::create([
                'requester_id' => $requesterId,
                'target_user_id' => $targetUserId,
                'original_date' => $data['original_date'],
                'swap_date' => $data['swap_date'] ?? null,
                'status' => 'PENDING_PEER',
            ]);

            // Notificar al peer
            $this->notifyUser($targetUserId, 'Nueva solicitud de intercambio de turno', $request);

            DB::commit();
            return $request;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error iniciando swap: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aprueba o rechaza la solicitud por el peer.
     *
     * @param int $requestId
     * @param int $userId Usuario que decide.
     * @param bool $approved
     * @return bool
     */
    public function peerDecision(int $requestId, int $userId, bool $approved): bool
    {
        $request = ShiftSwapRequest::findOrFail($requestId);

        if ($request->target_user_id !== $userId || $request->status !== 'PENDING_PEER') {
            throw new \InvalidArgumentException('Acción no permitida.');
        }

        $request->update([
            'status' => $approved ? 'PENDING_COORD' : 'REJECTED',
            'peer_accepted_at' => now(),
        ]);

        if ($approved) {
            // Notificar coordinador
            $coordId = $this->getCoordinatorId($request->requester_id);
            $this->notifyUser($coordId, 'Solicitud de swap pendiente de aprobación', $request);
        }

        return $approved;
    }

    /**
     * Aprobación por coordinador.
     *
     * @param int $requestId
     * @param int $coordId
     * @param bool $approved
     * @return bool
     */
    public function coordApproval(int $requestId, int $coordId, bool $approved): bool
    {
        $request = ShiftSwapRequest::findOrFail($requestId);

        if ($request->status !== 'PENDING_COORD') {
            throw new \InvalidArgumentException('Estado inválido.');
        }

        $request->update([
            'status' => $approved ? 'PENDING_WFM' : 'REJECTED',
            'coord_approved_at' => now(),
            'coord_user_id' => $coordId,
        ]);

        if ($approved) {
            // Notificar WFM
            $wfmId = $this->getWfmId();
            $this->notifyUser($wfmId, 'Solicitud de swap pendiente de aprobación final', $request);
        }

        return $approved;
    }

    /**
     * Aprobación final por WFM.
     *
     * @param int $requestId
     * @param int $wfmId
     * @param bool $approved
     * @return bool
     */
    public function wfmApproval(int $requestId, int $wfmId, bool $approved): bool
    {
        $request = ShiftSwapRequest::findOrFail($requestId);

        if ($request->status !== 'PENDING_WFM') {
            throw new \InvalidArgumentException('Estado inválido.');
        }

        $request->update([
            'status' => $approved ? 'APPROVED' : 'REJECTED',
            'wfm_approved_at' => now(),
            'wfm_user_id' => $wfmId,
        ]);

        if ($approved) {
            // Ejecutar el swap
            $this->executeSwap($request);
            // Notificar a todos
            $this->notifySwapCompleted($request);
        }

        return $approved;
    }

    /**
     * Ejecuta el intercambio de horarios.
     *
     * @param ShiftSwapRequest $request
     */
    private function executeSwap(ShiftSwapRequest $request): void
    {
        // Lógica para intercambiar schedules
        // Simplificado: asumir swap de fechas
        $originalSchedule = Schedule::where('employee_id', $request->requester_id)
            ->where('schedule_date', $request->original_date)
            ->first();

        $targetSchedule = Schedule::where('employee_id', $request->target_user_id)
            ->where('schedule_date', $request->swap_date ?? $request->original_date)
            ->first();

        if ($originalSchedule && $targetSchedule) {
            // Intercambiar shift_template_id o algo similar
            // Aquí implementar la lógica específica
            Log::info('Swap ejecutado para request ' . $request->id);
        }
    }

    /**
     * Obtiene ID del coordinador del usuario.
     *
     * @param int $userId
     * @return int
     */
    private function getCoordinatorId(int $userId): int
    {
        // Lógica para encontrar supervisor/coordinador
        // Simplificado
        return 1; // Placeholder
    }

    /**
     * Obtiene ID de un analista WFM.
     *
     * @return int
     */
    private function getWfmId(): int
    {
        // Simplificado
        return 2; // Placeholder
    }

    /**
     * Notifica a un usuario.
     *
     * @param int $userId
     * @param string $message
     * @param ShiftSwapRequest $request
     */
    private function notifyUser(int $userId, string $message, ShiftSwapRequest $request): void
    {
        // Usar Notification facade o email
        Log::info("Notificación a user $userId: $message");
    }

    /**
     * Notifica que el swap se completó.
     *
     * @param ShiftSwapRequest $request
     */
    private function notifySwapCompleted(ShiftSwapRequest $request): void
    {
        // Notificar requester, target, coord, wfm
    }
}