<?php
namespace App\Services;
use App\Models\CalculadoraSolicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformeGenerator
{
    public function generar(CalculadoraSolicitud $solicitud): string
    {
        return DB::transaction(function () use ($solicitud) {
            $fresh = CalculadoraSolicitud::lockForUpdate()->find($solicitud->id);

            if ($fresh->pdf_path && file_exists(storage_path("app/public/{$fresh->pdf_path}"))) {
                return $fresh->pdf_path;
            }

            $pdf  = Pdf::loadView('pdf.informe-ongrid', ['solicitud' => $fresh]);
            $name = 'informes/' . Str::uuid() . '.pdf';
            $path = storage_path("app/public/{$name}");

            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            try {
                $pdf->save($path);
            } catch (\Throwable $e) {
                throw new \RuntimeException("Error al guardar PDF del informe: {$e->getMessage()}", 0, $e);
            }

            $fresh->update(['pdf_path' => $name]);

            return $name;
        });
    }
}
