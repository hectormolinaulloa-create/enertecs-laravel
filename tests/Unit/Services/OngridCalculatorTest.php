<?php

namespace Tests\Unit\Services;

use App\Services\GoodweCatalog;
use App\Services\OngridCalculator;
use Tests\TestCase;

class OngridCalculatorTest extends TestCase
{
    private function input(): array
    {
        return [
            'consumo_kwh'               => 300,
            'region'                    => 'Metropolitana de Santiago',
            'tipo_medidor'              => 'monofasico',
            'panel'                     => [
                'id' => 'default', 'marca' => 'Jinko', 'modelo' => 'Tiger Neo',
                'potencia_wp' => 405, 'eficiencia_pct' => 21.3,
                'v_oc' => 37.8, 'v_mpp' => 31.5, 'i_sc' => 13.85, 'i_mpp' => 12.86,
                'coef_temp_pmax' => -0.29, 'largo_mm' => 1722, 'ancho_mm' => 1134, 'alto_mm' => 30,
                'tipo' => 'bifacial', 'certificacion_sec' => true, 'activo' => true,
            ],
            'inversores'                => GoodweCatalog::inversores(),
            'precio_kwh_clp'            => 158,
            'costo_referencial_kwp_clp' => 100000,
        ];
    }

    public function test_calcular_incluye_campos_economicos(): void
    {
        $result = app(OngridCalculator::class)->calcular($this->input());

        $this->assertArrayHasKey('costo_sin_solar_clp', $result);
        $this->assertArrayHasKey('costo_con_solar_clp', $result);
        $this->assertArrayHasKey('porcentaje_reduccion', $result);
    }

    public function test_costo_sin_solar_es_consumo_por_precio(): void
    {
        $result = app(OngridCalculator::class)->calcular($this->input());
        $this->assertEquals(round(300 * 158), $result['costo_sin_solar_clp']);
    }

    public function test_porcentaje_reduccion_entre_0_y_100(): void
    {
        $result = app(OngridCalculator::class)->calcular($this->input());
        $this->assertGreaterThan(0, $result['porcentaje_reduccion']);
        $this->assertLessThanOrEqual(100, $result['porcentaje_reduccion']);
    }

    public function test_costo_con_solar_no_es_negativo(): void
    {
        $result = app(OngridCalculator::class)->calcular($this->input());
        $this->assertGreaterThanOrEqual(0, $result['costo_con_solar_clp']);
    }
}
