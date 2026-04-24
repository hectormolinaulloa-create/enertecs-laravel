<?php
namespace App\Livewire;
use App\Jobs\ExtractBillJob;
use App\Models\CalculadoraSolicitud;
use App\Models\Configuracion;
use App\Services\OngridCalculator;
use Livewire\Component;
use Livewire\WithFileUploads;

class CalculadoraWizard extends Component
{
    use WithFileUploads;

    public int    $step         = 1;
    public        $pdf          = null;
    public ?int   $solicitudId  = null;
    public string $solicitudUuid = '';
    public string $jobEstado    = 'pendiente';
    public array  $datosBoleta = [];
    public array  $resultado   = [];
    public string $nombre      = '';
    public string $email       = '';
    public string $telefono    = '';
    public string $empresa     = '';
    public string $error       = '';

    // Step 1 → sube PDF y despacha job
    public function subirPdf(): void
    {
        $this->validate(['pdf' => 'required|file|mimes:pdf|max:10240']);

        $solicitud = null;
        try {
            $solicitud = CalculadoraSolicitud::create(['estado' => 'pendiente']);
            $path      = $this->pdf->store('boletas-tmp', 'local');
            ExtractBillJob::dispatch($solicitud, storage_path("app/{$path}"));
            $this->solicitudId   = $solicitud->id;
            $this->solicitudUuid = $solicitud->uuid;
            $this->step          = 2;
        } catch (\Throwable $e) {
            $solicitud?->delete();
            $this->error = 'Error al iniciar el análisis. Intenta nuevamente.';
        }
    }

    // Polling: Step 2 → espera que el job complete
    public function checkJobStatus(): void
    {
        if ($this->step !== 2 || !$this->solicitudId) return;

        $solicitud = CalculadoraSolicitud::find($this->solicitudId);
        if (!$solicitud) {
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

    // Step 3 → confirma datos, calcula resultado
    public function confirmarDatos(): void
    {
        $this->validate([
            'datosBoleta.consumo_kwh' => 'required|numeric|min:1',
            'datosBoleta.region'      => 'required|string',
        ]);

        try {
            $calc    = app(OngridCalculator::class);
            $this->resultado = $calc->calcular([
                'consumo_kwh'              => (float) $this->datosBoleta['consumo_kwh'],
                'region'                   => $this->datosBoleta['region'] ?? 'Metropolitana de Santiago',
                'tipo_medidor'             => $this->datosBoleta['tipo_medidor'] ?? 'monofasico',
                'panel'                    => $this->panelDefault(),
                'inversores'               => $this->inversoresDefault(),
                'precio_kwh_clp'           => (float) Configuracion::get('precio_kwh_clp', 158),
                'costo_referencial_kwp_clp'=> (float) Configuracion::get('costo_kwp_clp', 650000),
            ]);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            return;
        }

        CalculadoraSolicitud::find($this->solicitudId)?->update([
            'datos_boleta' => $this->datosBoleta,
            'resultado'    => $this->resultado,
        ]);

        $this->step = 4;
    }

    // Step 4 → guarda contacto
    public function guardarContacto(): void
    {
        $this->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'nullable|email:rfc|max:150',
            'telefono' => 'required|string|max:20',
        ]);

        CalculadoraSolicitud::find($this->solicitudId)?->update([
            'nombre'   => $this->nombre,
            'email'    => $this->email,
            'telefono' => $this->telefono,
            'empresa'  => $this->empresa,
        ]);

        $this->step = 5;
    }

    public function reiniciar(): void
    {
        $this->step = 1;
        $this->pdf = null;
        $this->solicitudId = null;
        $this->solicitudUuid = '';
        $this->jobEstado = 'pendiente';
        $this->datosBoleta = [];
        $this->resultado = [];
        $this->nombre = '';
        $this->email = '';
        $this->telefono = '';
        $this->empresa = '';
        $this->error = '';
    }

    public function render()
    {
        return view('livewire.calculadora-wizard');
    }

    // ── Helpers datos de equipos por defecto ──────────────────────────────
    private function panelDefault(): array
    {
        return [
            'id' => 'default', 'marca' => 'Jinko', 'modelo' => 'Tiger Neo',
            'potencia_wp' => 405, 'eficiencia_pct' => 21.3,
            'v_oc' => 37.8, 'v_mpp' => 31.5, 'i_sc' => 13.85, 'i_mpp' => 12.86,
            'coef_temp_pmax' => -0.29, 'largo_mm' => 1722, 'ancho_mm' => 1134, 'alto_mm' => 30,
            'tipo' => 'bifacial', 'certificacion_sec' => true, 'activo' => true,
        ];
    }

    private function inversoresDefault(): array
    {
        return [[
            'id' => 'default', 'marca' => 'Growatt', 'modelo' => 'MIN 6000TL-X',
            'potencia_kw' => 6.0, 'fases' => 'monofasico', 'num_mppt' => 2,
            'v_mppt_min' => 80, 'v_mppt_max' => 600, 'corriente_max_dc' => 25,
            'potencia_max_dc_kw' => 8.0, 'eficiencia_max_pct' => 97.0, 'activo' => true,
        ]];
    }
}
