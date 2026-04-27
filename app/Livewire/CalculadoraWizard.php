<?php
namespace App\Livewire;

use App\Jobs\ExtractBillJob;
use App\Models\CalculadoraSolicitud;
use App\Models\Configuracion;
use App\Services\GoodweCatalog;
use App\Services\OngridCalculator;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CalculadoraWizard extends Component
{
    use WithFileUploads;

    public int    $step          = 1;
    public        $pdf           = null;
    public ?int   $solicitudId   = null;
    public string $solicitudUuid = '';
    public string $jobEstado     = 'pendiente';
    public array  $datosBoleta   = [];
    public array  $resultado     = [];
    public string $error         = '';

    // Step 1 → sube PDF y despacha job
    public function subirPdf(): void
    {
        $this->validate(['pdf' => 'required|file|mimes:pdf|max:10240']);

        $solicitud = null;
        try {
            $solicitud = CalculadoraSolicitud::create(['estado' => 'pendiente']);
            $path      = $this->pdf->store('boletas-tmp', 'local');
            ExtractBillJob::dispatch($solicitud, Storage::disk('local')->path($path));
            $this->solicitudId   = $solicitud->id;
            $this->solicitudUuid = $solicitud->uuid;
            $this->step          = 2;
        } catch (\Throwable $e) {
            $solicitud?->delete();
            $this->error = 'Error al iniciar el análisis. Intenta nuevamente.';
        }
    }

    // Step 2 → polling hasta que el job complete
    public function checkJobStatus(): void
    {
        if ($this->step !== 2 || ! $this->solicitudId) return;

        $solicitud = CalculadoraSolicitud::find($this->solicitudId);
        if (! $solicitud) {
            $this->error = 'Sesión expirada. Por favor, sube la boleta nuevamente.';
            $this->step  = 1;
            return;
        }

        $this->jobEstado = $solicitud->estado;

        if ($solicitud->estado === 'completado') {
            $this->datosBoleta = $solicitud->datos_boleta ?? [];
            $this->step        = 3;
        } elseif ($solicitud->estado === 'error') {
            $this->error = 'No pudimos leer la boleta. Intenta con otro archivo o ingresa los datos manualmente.';
            $this->step  = 1;
        }
    }

    // Step 3 → confirma datos del cliente + calcula
    public function confirmarDatos(): void
    {
        $this->validate([
            'datosBoleta.nombre_cliente' => 'required|string|max:100',
            'datosBoleta.region'         => 'required|string',
            'datosBoleta.telefono'       => 'required|string|max:20',
            'datosBoleta.email'          => 'required|email:rfc|max:150',
        ]);

        try {
            $calc           = app(OngridCalculator::class);
            $this->resultado = $calc->calcular([
                'consumo_kwh'               => (float) ($this->datosBoleta['consumo_efectivo'] ?? $this->datosBoleta['consumo_kwh'] ?? 0),
                'region'                    => $this->datosBoleta['region'] ?? 'Metropolitana de Santiago',
                'tipo_medidor'              => $this->datosBoleta['tipo_medidor'] ?? 'monofasico',
                'panel'                     => $this->panelDefault(),
                'inversores'                => $this->inversoresDefault(),
                'precio_kwh_clp'            => (float) ($this->datosBoleta['precio_kwh_clp'] ?? Configuracion::get('precio_kwh_clp', 158)),
                'costo_referencial_kwp_clp' => (float) Configuracion::get('costo_kwp_clp', 650000),
            ]);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            return;
        }

        CalculadoraSolicitud::find($this->solicitudId)?->update([
            'nombre'       => $this->datosBoleta['nombre_cliente'] ?? '',
            'email'        => $this->datosBoleta['email'] ?? '',
            'telefono'     => $this->datosBoleta['telefono'] ?? '',
            'empresa'      => $this->datosBoleta['empresa'] ?? '',
            'datos_boleta' => $this->datosBoleta,
            'resultado'    => $this->resultado,
        ]);

        $this->step = 4;
    }

    public function reiniciar(): void
    {
        $this->step          = 1;
        $this->pdf           = null;
        $this->solicitudId   = null;
        $this->solicitudUuid = '';
        $this->jobEstado     = 'pendiente';
        $this->datosBoleta   = [];
        $this->resultado     = [];
        $this->error         = '';
    }

    public function render()
    {
        return view('livewire.calculadora-wizard');
    }

    private function panelDefault(): array
    {
        return [
            'id' => 'default', 'marca' => 'Jinko', 'modelo' => 'Tiger Neo',
            'potencia_wp' => 405, 'eficiencia_pct' => 21.3,
            'v_oc' => 37.8, 'v_mpp' => 31.5, 'i_sc' => 13.85, 'i_mpp' => 12.86,
            'coef_temp_pmax' => -0.29, 'largo_mm' => 1722, 'ancho_mm' => 1134, 'alto_mm' => 30,
            'peso_kg' => 21.3,
            'tipo' => 'bifacial', 'certificacion_sec' => true, 'activo' => true,
        ];
    }

    private function inversoresDefault(): array
    {
        return GoodweCatalog::inversores();
    }
}
