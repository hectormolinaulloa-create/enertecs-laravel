<?php
namespace App\Http\Controllers;
use App\Models\CalculadoraSolicitud;
use App\Services\InformeGenerator;
use App\Services\VrmClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    public function vrmChart(Request $request): JsonResponse
    {
        $range = in_array($request->get('range'), ['24h', '72h', '7d', '14d', '30d'])
            ? $request->get('range') : '24h';

        $seconds = ['24h' => 86400, '72h' => 259200, '7d' => 604800, '14d' => 1209600, '30d' => 2592000];
        $ttl     = ['24h' => 900, '72h' => 1800, '7d' => 3600, '14d' => 7200, '30d' => 14400];

        $key = "vrm:chart:{$range}";
        try {
            $data = Cache::remember($key, $ttl[$range], function () use ($range, $seconds) {
                $client  = app(VrmClient::class);
                $idUser  = $client->login();
                $rawList = $client->getInstallations($idUser);
                $whitelist = array_keys(\App\Livewire\VrmDashboard::getWhitelist());

                $now   = time();
                $start = $now - $seconds[$range];
                $interval = in_array($range, ['24h', '72h']) ? 'hours' : 'days';

                $prodMap = [];
                $consMap = [];

                foreach ($rawList as $r) {
                    $siteId = $r['idSite'] ?? null;
                    if (!$siteId || !in_array($siteId, $whitelist)) continue;
                    try {
                        $records = $client->getHourlyStats($siteId, $start, $now);
                        foreach ($records['kwh'] ?? [] as [$ts, $kwh]) {
                            $bucket = $interval === 'hours' ? intval($ts / 3600) * 3600 : intval($ts / 86400) * 86400;
                            $prodMap[$bucket] = ($prodMap[$bucket] ?? 0) + $kwh;
                        }
                        foreach ($records['ac_consumption'] ?? [] as [$ts, $kwh]) {
                            $bucket = $interval === 'hours' ? intval($ts / 3600) * 3600 : intval($ts / 86400) * 86400;
                            $consMap[$bucket] = ($consMap[$bucket] ?? 0) + $kwh;
                        }
                    } catch (\Throwable) {}
                }

                ksort($prodMap);
                ksort($consMap);

                $toPoints = fn($map) => array_map(
                    fn($ts, $kwh) => ['ts' => $ts * 1000, 'kwh' => round($kwh, 2)],
                    array_keys($map), array_values($map)
                );

                return [
                    'range'      => $range,
                    'production' => $toPoints($prodMap),
                    'consumption'=> $toPoints($consMap),
                ];
            });
            return response()->json($data);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'VRM no disponible'], 503);
        }
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
