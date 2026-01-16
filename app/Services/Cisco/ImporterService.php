<?php

namespace App\Services\Cisco;

use App\Models\AhtCallsRaw;
use App\Models\CallsRaw;
use App\Models\AgentStateNotreadyRaw;
use App\Models\ChatConversationsRaw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImporterService
{
    /**
     * Importa datos desde un archivo CSV a la tabla correspondiente.
     *
     * @param string $filePath Ruta al archivo CSV.
     * @param string $type Tipo de archivo ('aht', 'calls', 'notready', 'chat').
     * @return bool Éxito de la importación.
     */
    public function importCsv(string $filePath, string $type): bool
    {
        try {
            DB::beginTransaction();

            $data = $this->parseCsv($filePath, $type);

            switch ($type) {
                case 'aht':
                    AhtCallsRaw::insert($data);
                    break;
                case 'calls':
                    CallsRaw::insert($data);
                    break;
                case 'notready':
                    AgentStateNotreadyRaw::insert($data);
                    break;
                case 'chat':
                    ChatConversationsRaw::insert($data);
                    break;
                default:
                    throw new \InvalidArgumentException("Tipo de archivo no soportado: $type");
            }

            DB::commit();

            // Eliminar archivo tras éxito
            unlink($filePath);

            Log::info("Archivo $type importado exitosamente: $filePath");

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error importando archivo $type: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parsea el CSV y lo convierte a array para inserción.
     *
     * @param string $filePath
     * @param string $type
     * @return array
     */
    private function parseCsv(string $filePath, string $type): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');

        // Saltar header
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $record = $this->mapRowToRecord($row, $type);
            if ($record) {
                $data[] = $record;
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Mapea una fila CSV a un array para la BD.
     *
     * @param array $row
     * @param string $type
     * @return array|null
     */
    private function mapRowToRecord(array $row, string $type): ?array
    {
        // Implementar mapeo según estructura CSV en BASE.md
        // Ejemplo simplificado para AHT
        if ($type === 'aht') {
            return [
                'nombre_del_agente' => $row[0] ?? null,
                'id_de_conexion_del_agente' => $row[1] ?? null,
                'agent_ext' => $row[2] ?? null,
                'hora_de_inicio_de_llamada' => $this->parseDate($row[3] ?? null),
                'hora_de_fin_de_llamada' => $this->parseDate($row[4] ?? null),
                'duracion_de_llamada' => (int)($row[5] ?? 0),
                'numero_llamado' => $row[6] ?? null,
                'ani_de_llamada' => $row[7] ?? null,
                'llamada_dirigida_por_csq' => $row[8] ?? null,
                'other_csq' => $row[9] ?? null,
                'call_skill' => $row[10] ?? null,
                'tiempo_de_conversacion' => (int)($row[11] ?? 0),
                'tiempo_en_espera' => (int)($row[12] ?? 0),
                'tiempo_de_cierre' => (int)($row[13] ?? 0),
                'call_type' => $row[14] ?? null,
                'created_at' => now(),
            ];
        }

        // Implementar para otros tipos...
        return null;
    }

    /**
     * Parsea fechas del CSV (ej: "Fri Nov 07 15:07:08 EST 2025").
     *
     * @param string|null $dateStr
     * @return string|null
     */
    private function parseDate(?string $dateStr): ?string
    {
        if (!$dateStr) return null;

        try {
            // Convertir a Carbon y a UTC
            return \Carbon\Carbon::createFromFormat('D M d H:i:s T Y', $dateStr)->utc()->toDateTimeString();
        } catch (\Exception $e) {
            Log::warning("Fecha inválida: $dateStr");
            return null;
        }
    }
}