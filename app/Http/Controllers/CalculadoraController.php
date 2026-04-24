<?php
namespace App\Http\Controllers;
use App\Models\CalculadoraSolicitud;
use App\Services\InformeGenerator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalculadoraController extends Controller
{
    public function jobStatus(CalculadoraSolicitud $solicitud): JsonResponse
    {
        return response()->json([
            'estado'       => $solicitud->estado,
            'datos_boleta' => $solicitud->estado === 'completado' ? $solicitud->datos_boleta : null,
        ]);
    }

    public function descargarInforme(CalculadoraSolicitud $solicitud, InformeGenerator $generator): BinaryFileResponse
    {
        abort_unless($solicitud->estado === 'completado', 404);
        try {
            $path = $generator->generar($solicitud);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Error generando el informe. Intente nuevamente.');
        }
        return response()->download(storage_path("app/public/{$path}"), "informe-solar-{$solicitud->id}.pdf");
    }
}
