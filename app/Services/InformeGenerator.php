<?php
namespace App\Services;
use App\Models\CalculadoraSolicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InformeGenerator
{
    public function generar(CalculadoraSolicitud $solicitud): string
    {
        if ($solicitud->pdf_path && file_exists(storage_path("app/public/{$solicitud->pdf_path}"))) {
            return $solicitud->pdf_path;
        }

        $pdf  = Pdf::loadView('pdf.informe-ongrid', ['solicitud' => $solicitud]);
        $name = 'informes/' . Str::uuid() . '.pdf';
        $path = storage_path("app/public/{$name}");

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);
        $solicitud->update(['pdf_path' => $name]);

        return $name;
    }
}
