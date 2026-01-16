<?php

namespace App\Http\Controllers;

use App\Services\Cisco\ImporterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    private ImporterService $importerService;

    public function __construct(ImporterService $importerService)
    {
        $this->importerService = $importerService;
    }

    /**
     * Importa un archivo CSV.
     *
     * @param Request $request
     * @param string $type
     * @return JsonResponse
     */
    public function import(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $filePath = $file->storeAs('imports', $type . '_' . time() . '.csv');

        $success = $this->importerService->importCsv(storage_path('app/' . $filePath), $type);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Archivo importado exitosamente' : 'Error en la importación',
        ]);
    }
}