<?php

namespace App\Livewire;

use App\Services\VrmClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class VrmDashboard extends Component
{
    public array  $snapshot  = [];
    public bool   $error     = false;
    public string $errorMsg  = '';

    // Whitelist de instalaciones visibles (external_id → [nombre, region, lat, lng])
    public const WHITELIST = [
        246959 => ['nombre' => 'Cerro Guido 1',       'region' => 'Magallanes', 'lat' => -51.6893, 'lng' => -72.4164],
        281682 => ['nombre' => 'Cerro Guido 2',       'region' => 'Magallanes', 'lat' => -51.6893, 'lng' => -72.4164],
        276532 => ['nombre' => 'Río Serrano',         'region' => 'Magallanes', 'lat' => -51.0340, 'lng' => -72.9694],
        292418 => ['nombre' => 'Rio Serrano 2',       'region' => 'Magallanes', 'lat' => -51.0340, 'lng' => -72.9694],
        296262 => ['nombre' => 'Gastón en Laguna Sofia', 'region' => 'Magallanes', 'lat' => -52.0333, 'lng' => -72.2167],
        395217 => ['nombre' => 'Faro Dungeness',      'region' => 'Magallanes', 'lat' => -52.4012, 'lng' => -68.4352],
        841230 => ['nombre' => 'Faro San Pedro',      'region' => 'Magallanes', 'lat' => -52.9885, 'lng' => -70.8669],
        485461 => ['nombre' => 'Las Torres',          'region' => 'Magallanes', 'lat' => -50.9427, 'lng' => -72.9428],
        413986 => ['nombre' => 'Laboratorio',         'region' => 'Magallanes', 'lat' => -53.1558, 'lng' => -70.9147],
        816410 => ['nombre' => 'Jorge Cañón',         'region' => 'Magallanes', 'lat' => -53.1558, 'lng' => -70.9147],
        794121 => ['nombre' => 'Peter MacClean',      'region' => 'Magallanes', 'lat' => -53.1558, 'lng' => -70.9147],
    ];

    private const CO2_FACTOR = 0.4;
    private const CACHE_TTL  = 1800; // 30 minutos

    public static function getWhitelist(): array { return self::WHITELIST; }

    public function mount(): void
    {
        $this->loadSnapshot();
    }

    public function fetchData(): void
    {
        Cache::forget('vrm:snapshot');
        $this->loadSnapshot();
    }

    private function loadSnapshot(): void
    {
        if (!config('services.vrm.token')) {
            $this->error    = true;
            $this->errorMsg = 'VRM_TOKEN no configurado.';
            return;
        }

        try {
            $this->snapshot = Cache::remember('vrm:snapshot', self::CACHE_TTL, fn() => $this->buildSnapshot());
            $this->error = false;
        } catch (\Throwable $e) {
            $this->error    = true;
            $this->errorMsg = 'No se pudo conectar con el sistema VRM.';
            report($e);
        }
    }

    private function buildSnapshot(): array
    {
        $client = app(VrmClient::class);
        $idUser = $client->login();
        $rawList = $client->getInstallations($idUser);

        $now   = time();
        $start30d = $now - 30 * 86400;

        $solarKwh      = 0.0;
        $capacidadWp   = 0;
        $plantasActivas = 0;
        $pvWattsLive   = 0;
        $instalaciones  = [];
        $dailyTotals    = [];

        foreach ($rawList as $r) {
            $siteId = $r['idSite'] ?? null;
            if (!$siteId || !isset(self::WHITELIST[$siteId])) continue;

            $wl = self::WHITELIST[$siteId];
            $capacidadWp += $r['pvMax'] ?? 0;

            // ── Producción anual + trend 30d ─────────────────────────────
            $siteDaily = [];
            try {
                $overall = $client->getOverallStats($siteId);
                $solarKwh += $overall['year']['totals']['kwh'] ?? 0;

                $daily = $client->getDailyStats($siteId, $start30d, $now);
                foreach ($daily['kwh'] ?? [] as [$ts, $kwh]) {
                    $date = date('Y-m-d', $ts < 1e12 ? $ts : intval($ts / 1000));
                    $siteDaily[$date] = ($siteDaily[$date] ?? 0) + $kwh;
                    $dailyTotals[$date] = ($dailyTotals[$date] ?? 0) + $kwh;
                }
            } catch (\Throwable) {}

            // Previsión = media últimos 7 días disponibles para este sitio
            $last7 = array_slice(array_values($siteDaily), -7);
            $previsionKwh = count($last7) > 0 ? round(array_sum($last7) / count($last7)) : 0;

            // ── Estado online/offline ────────────────────────────────────
            $estado = 'offline';
            try {
                $statusW = $client->getStatusWidget($siteId);
                $data = $statusW['data'] ?? [];
                if ($data['hasOldData'] ?? false) {
                    $estado = 'offline';
                } elseif ($r['alarm'] ?? false) {
                    $estado = 'alarm';
                } else {
                    $secsAgo = $statusW['data']['secondsAgo']['value'] ?? PHP_INT_MAX;
                    $estado  = $secsAgo <= 900 ? 'online' : 'offline';
                }
            } catch (\Throwable) {}
            if ($estado === 'online') $plantasActivas++;

            // ── Datos en vivo (SOC, PV power, yield today) ───────────────
            $socPct    = null;
            $pvWatts   = 0;
            $genHoyKwh = 0.0;
            try {
                $diags = $client->getDiagnostics($siteId);
                foreach ($diags as $rec) {
                    if ($rec['rawValue'] === null) continue;
                    $d = strtolower($rec['description'] ?? '');
                    if (str_contains($d, 'state of charge') && !str_contains($d, 'target'))
                        $socPct = (int) round($rec['rawValue']);
                    elseif (str_contains($d, 'solar charger pv power') || str_contains($d, 'pv - dc-coupled'))
                        $pvWatts += (int) round($rec['rawValue']);
                    elseif (str_contains($d, 'yield today'))
                        $genHoyKwh += (float) $rec['rawValue'];
                }
            } catch (\Throwable) {}
            $pvWattsLive += $pvWatts;

            $instalaciones[] = [
                'id_site'      => $siteId,
                'nombre'       => $wl['nombre'],
                'region'       => $wl['region'],
                'lat'          => $wl['lat'],
                'lng'          => $wl['lng'],
                'estado'       => $estado,
                'soc_pct'      => $socPct,
                'pv_watts'     => $pvWatts,
                'gen_hoy_kwh'  => round($genHoyKwh, 1),
                'prevision_kwh'=> $previsionKwh,
            ];
        }

        ksort($dailyTotals);
        $trend30d = array_map(
            fn($date, $kwh) => ['date' => $date, 'kwh' => round($kwh, 2)],
            array_keys($dailyTotals),
            array_values($dailyTotals),
        );
        $trend30d = array_slice($trend30d, -30);

        return [
            'generated_at'  => now()->toIso8601String(),
            'totals' => [
                'solar_kwh'       => round($solarKwh, 2),
                'co2_kg'          => round($solarKwh * self::CO2_FACTOR, 2),
                'capacidad_wp'    => $capacidadWp,
                'plantas_activas' => $plantasActivas,
                'plantas_total'   => count($instalaciones),
                'pv_watts_live'   => $pvWattsLive,
            ],
            'trend30d'      => $trend30d,
            'instalaciones' => $instalaciones,
        ];
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="py-16 text-center text-white/40 text-sm tracking-widest animate-pulse">
            CARGANDO DATOS EN VIVO…
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.vrm-dashboard');
    }
}
