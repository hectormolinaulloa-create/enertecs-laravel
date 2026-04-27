<?php

namespace App\Services;

class OngridCalculator
{
    private const PERDIDA_CABLEADO    = 0.98;
    private const PERDIDA_SUCIEDAD    = 0.97;
    private const PERDIDA_TEMPERATURA = 0.97;
    private const PERDIDA_MISMATCH    = 0.98;
    private const FACTOR_CO2          = 0.295;
    private const GAIN_BIFACIAL       = 1.07;
    private const T_MIN_CELDA         = -5;
    private const T_MAX_CELDA         = 75;

    private const HSP = [
        'Arica y Parinacota'        => 7.2,
        'Tarapacá'                  => 7.0,
        'Antofagasta'               => 6.8,
        'Atacama'                   => 6.5,
        'Coquimbo'                  => 5.8,
        'Valparaíso'                => 5.3,
        'Metropolitana de Santiago' => 5.5,
        "O'Higgins"                 => 5.2,
        'Maule'                     => 4.8,
        'Ñuble'                     => 4.6,
        'Biobío'                    => 4.4,
        'La Araucanía'              => 4.2,
        'Los Ríos'                  => 3.9,
        'Los Lagos'                 => 3.7,
        'Aysén'                     => 3.6,
        'Magallanes'                => 3.5,
    ];

    /**
     * Calcula el número de paneles por string dentro del rango MPPT del inversor.
     * Retorna 0 si el panel es eléctricamente incompatible.
     */
    private function calcPanelesXString(array $panel, array $inversor): int
    {
        if (($panel['v_oc'] ?? 0) == 0 || ($panel['v_mpp'] ?? 0) == 0) {
            return 1;
        }

        $coefVoc  = ($panel['coef_temp_pmax'] ?? -0.29) * 0.80;
        $coefVmpp = ($panel['coef_temp_pmax'] ?? -0.29);

        $vocTmin  = $panel['v_oc']  * (1 + $coefVoc  * (self::T_MIN_CELDA - 25) / 100);
        $vmppTmax = $panel['v_mpp'] * (1 + $coefVmpp * (self::T_MAX_CELDA - 25) / 100);

        if ($vocTmin <= 0 || $vmppTmax <= 0) return 1;

        $nMax = (int) floor($inversor['v_mppt_max'] / $vocTmin);
        $nMin = (int) ceil($inversor['v_mppt_min']  / $vmppTmax);

        if ($nMax < $nMin || $nMax < 1) return 0;

        $objetivo = min(12, $nMax);
        return max($objetivo, $nMin);
    }

    /**
     * @param array $input {
     *   consumo_kwh: float,
     *   region: string,
     *   tipo_medidor: 'monofasico'|'trifasico',
     *   panel: array,
     *   inversores: array[],
     *   precio_kwh_clp: float,
     *   costo_referencial_kwp_clp: float
     * }
     * @throws \RuntimeException Si no hay inversor compatible disponible.
     */
    public function calcular(array $input): array
    {
        $consumo    = (float) $input['consumo_kwh'];
        $region     = $input['region'];
        $tipo       = $input['tipo_medidor'];
        $panel      = $input['panel'];
        $inversores = $input['inversores'];
        $precioKwh      = (float) $input['precio_kwh_clp'];
        $costoPorPanel  = (float) $input['costo_referencial_kwp_clp']; // precio por panel (nombre del campo heredado del TS)

        $hsp = self::HSP[$region] ?? 4.5;

        // Performance Ratio
        $candidatosPrev = array_filter($inversores, fn($i) => ($i['fases'] ?? '') === $tipo && ($i['activo'] ?? true));
        $efInversor = count($candidatosPrev) > 0
            ? max(array_column(array_values($candidatosPrev), 'eficiencia_max_pct')) / 100
            : 0.97;

        $gain = ($panel['tipo'] ?? 'monofacial') === 'bifacial' ? self::GAIN_BIFACIAL : 1.0;
        $pr   = $efInversor
               * self::PERDIDA_CABLEADO
               * self::PERDIDA_SUCIEDAD
               * self::PERDIDA_TEMPERATURA
               * self::PERDIDA_MISMATCH
               * $gain;

        // Dimensionamiento
        $potenciaSistema = $consumo / ($hsp * 30 * $pr);
        $potenciaPanel   = ($panel['potencia_wp'] ?? 400) / 1000;
        $nPaneles        = (int) ceil($potenciaSistema / $potenciaPanel);
        $potenciaReal    = $nPaneles * $potenciaPanel;

        // Selección de inversor
        $potenciaAcMin = $potenciaReal * 0.80;
        $aptos = array_values(array_filter($inversores, fn($i) =>
            ($i['fases'] ?? '') === $tipo &&
            ($i['activo'] ?? true) &&
            ($i['potencia_kw'] ?? 0) >= $potenciaAcMin
        ));
        usort($aptos, fn($a, $b) => ($a['potencia_kw'] ?? 0) <=> ($b['potencia_kw'] ?? 0));

        // Cuando ningún inversor cubre la potencia requerida, usar el más grande en paralelo
        $nInversores = 1;
        if (empty($aptos)) {
            $todosDelTipo = array_values(array_filter($inversores, fn($i) =>
                ($i['fases'] ?? '') === $tipo && ($i['activo'] ?? true)
            ));
            if (empty($todosDelTipo)) {
                throw new \RuntimeException("No hay inversores {$tipo} disponibles en el catálogo.");
            }
            usort($todosDelTipo, fn($a, $b) => ($b['potencia_kw'] ?? 0) <=> ($a['potencia_kw'] ?? 0));
            $inversorMax = $todosDelTipo[0];
            $nInversores = (int) ceil($potenciaAcMin / max(0.1, $inversorMax['potencia_kw'] ?? 1));
            $aptos       = [$inversorMax];
        }

        $inversor         = null;
        $panelesPorString = 1;
        $nStrings         = 1;

        // Para multi-inversor: cada unidad maneja n_paneles / nInversores paneles
        $panelesPorInversor = (int) ceil($nPaneles / $nInversores);

        foreach ($aptos as $candidato) {
            $sinDatosElectricos = ($panel['v_oc'] ?? 0) == 0 || ($panel['v_mpp'] ?? 0) == 0;

            if ($sinDatosElectricos) {
                $inversor         = $candidato;
                $panelesPorString = $panelesPorInversor;
                $nStrings         = $nInversores;
                break;
            }

            $pxs = $this->calcPanelesXString($panel, $candidato);
            if ($pxs === 0) continue;

            $stringsNecesarios = (int) ceil($panelesPorInversor / $pxs);
            $corrientePorMppt  = ($panel['i_sc'] ?? 0) * ceil($stringsNecesarios / max(1, $candidato['num_mppt'] ?? 1));

            if ($corrientePorMppt > ($candidato['corriente_max_dc'] ?? 999) * 1.05) continue;
            $potenciaRealPorInversor = $potenciaReal / $nInversores;
            if (($candidato['potencia_max_dc_kw'] ?? 0) > 0 && $potenciaRealPorInversor > $candidato['potencia_max_dc_kw']) continue;

            $inversor         = $candidato;
            $panelesPorString = $pxs;
            $nStrings         = $stringsNecesarios * $nInversores;
            break;
        }

        if (!$inversor) {
            $inversor         = end($aptos);
            $pxs              = $this->calcPanelesXString($panel, $inversor);
            $panelesPorString = $pxs > 0 ? $pxs : 1;
            $nStrings         = (int) ceil($nPaneles / $panelesPorString);
        }

        // Producción y economía
        $produccionMensual = $potenciaReal * $hsp * 30 * $pr;
        $ahorroMensual     = $produccionMensual * $precioKwh;
        $costoSistema      = $nPaneles * $costoPorPanel;
        $roiAnos           = $ahorroMensual > 0 ? $costoSistema / ($ahorroMensual * 12) : 0;
        $co2KgAnual        = $produccionMensual * 12 * self::FACTOR_CO2;
        $areaM2            = $nPaneles * (($panel['largo_mm'] ?? 1722) / 1000) * (($panel['ancho_mm'] ?? 1134) / 1000);

        return [
            'potencia_sistema_kwp'   => round($potenciaSistema, 4),
            'n_paneles'              => $nPaneles,
            'panel'                  => $panel,
            'potencia_real_kwp'      => round($potenciaReal, 4),
            'inversor'               => $inversor,
            'n_inversores'           => $nInversores,
            'paneles_por_string'     => $panelesPorString,
            'n_strings'              => $nStrings,
            'produccion_mensual_kwh' => round($produccionMensual, 2),
            'ahorro_mensual_clp'     => round($ahorroMensual),
            'roi_anos'               => round($roiAnos, 2),
            'co2_kg_anual'           => round($co2KgAnual),
            'area_m2'                => round($areaM2, 2),
            'pr'                     => round($pr, 4),
            'hsp'                    => $hsp,
            'region'                 => $region,
        ];
    }
}
