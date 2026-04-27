<?php
namespace App\Services;

use App\Models\Configuracion;

class BillNormalizer
{
    private const REGIONES_VALIDAS = [
        'Arica y Parinacota', 'Tarapacá', 'Antofagasta', 'Atacama', 'Coquimbo',
        'Valparaíso', 'Metropolitana de Santiago', "O'Higgins", 'Maule', 'Ñuble',
        'Biobío', 'La Araucanía', 'Los Ríos', 'Los Lagos', 'Aysén', 'Magallanes',
    ];

    public function normalize(array $raw): array
    {
        $raw['precio_kwh_clp']   = $this->calcPrecioKwh($raw);
        $raw['consumo_efectivo'] = $this->calcConsumoEfectivo($raw);
        $raw['region']           = $this->resolveRegion($raw);
        return $raw;
    }

    private function calcPrecioKwh(array $raw): float
    {
        $monto   = (float) ($raw['monto_total_clp'] ?? 0);
        $consumo = (float) ($raw['consumo_kwh'] ?? 0);

        if ($monto > 0 && $consumo > 0) {
            return round($monto / $consumo, 4);
        }
        return (float) Configuracion::get('precio_kwh_clp', 158);
    }

    private function calcConsumoEfectivo(array $raw): float
    {
        $historial = $raw['consumo_historico_kwh'] ?? null;

        if (empty($historial)) {
            return (float) ($raw['consumo_kwh'] ?? 0);
        }

        rsort($historial);
        $top = array_slice($historial, 0, 6);
        return round(array_sum($top) / count($top), 2);
    }

    private function resolveRegion(array $raw): ?string
    {
        $regionClaude = $raw['region'] ?? null;
        if ($regionClaude && in_array($regionClaude, self::REGIONES_VALIDAS, true)) {
            return $regionClaude;
        }

        $comuna = (string) ($raw['comuna'] ?? '');
        return ComunaRegionMap::lookup($comuna);
    }
}
