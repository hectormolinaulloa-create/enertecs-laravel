<?php
namespace App\Jobs;
use App\Models\CalculadoraSolicitud;
use App\Services\BillExtractor;
use App\Services\BillNormalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractBillJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        public CalculadoraSolicitud $solicitud,
        public string               $pdfPath,
    ) {}

    public function handle(BillExtractor $extractor, BillNormalizer $normalizer): void
    {
        $this->solicitud->update(['estado' => 'procesando']);
        try {
            $data = $extractor->extract($this->pdfPath);
            $data = $normalizer->normalize($data);
            $this->solicitud->update([
                'datos_boleta' => $data,
                'estado'       => 'completado',
            ]);
            $this->deletePdf();
        } catch (\Throwable $e) {
            $this->solicitud->update(['estado' => 'error']);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->solicitud->update(['estado' => 'error']);
        $this->deletePdf();
    }

    private function deletePdf(): void
    {
        if (file_exists($this->pdfPath)) {
            unlink($this->pdfPath);
        }
    }
}
