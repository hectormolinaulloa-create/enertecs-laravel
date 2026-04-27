<?php
namespace Tests\Unit\Services;

use App\Services\BillNormalizer;
use Tests\TestCase;

class BillNormalizerTest extends TestCase
{
    private BillNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new BillNormalizer();
    }

    // ── consumo_efectivo ──────────────────────────────────────────────────

    public function test_consumo_efectivo_promedia_top_6_de_historial_largo(): void
    {
        $raw = $this->baseRaw([
            'consumo_kwh'             => 100,
            'consumo_historico_kwh'   => [100, 200, 300, 400, 500, 600, 50, 30, 80, 120, 150, 180],
        ]);
        // top 6: [600,500,400,300,200,180] → suma 2180 / 6 = 363.33
        $result = $this->normalizer->normalize($raw);
        $this->assertEqualsWithDelta(363.33, $result['consumo_efectivo'], 0.01);
    }

    public function test_consumo_efectivo_usa_todos_si_hay_menos_de_6(): void
    {
        $raw = $this->baseRaw([
            'consumo_kwh'           => 100,
            'consumo_historico_kwh' => [200, 300, 400],
        ]);
        // promedio de [400, 300, 200] = 300
        $result = $this->normalizer->normalize($raw);
        $this->assertEqualsWithDelta(300.0, $result['consumo_efectivo'], 0.01);
    }

    public function test_consumo_efectivo_usa_consumo_kwh_cuando_no_hay_historial(): void
    {
        $raw = $this->baseRaw([
            'consumo_kwh'           => 250,
            'consumo_historico_kwh' => null,
        ]);
        $result = $this->normalizer->normalize($raw);
        $this->assertEqualsWithDelta(250.0, $result['consumo_efectivo'], 0.01);
    }

    public function test_consumo_efectivo_usa_consumo_kwh_cuando_historial_vacio(): void
    {
        $raw = $this->baseRaw([
            'consumo_kwh'           => 180,
            'consumo_historico_kwh' => [],
        ]);
        $result = $this->normalizer->normalize($raw);
        $this->assertEqualsWithDelta(180.0, $result['consumo_efectivo'], 0.01);
    }

    // ── precio_kwh_clp ────────────────────────────────────────────────────

    public function test_precio_kwh_se_calcula_desde_boleta(): void
    {
        $raw = $this->baseRaw(['monto_total_clp' => 54000, 'consumo_kwh' => 300]);
        $result = $this->normalizer->normalize($raw);
        $this->assertEqualsWithDelta(180.0, $result['precio_kwh_clp'], 0.01);
    }

    public function test_precio_kwh_usa_config_si_consumo_es_cero(): void
    {
        $raw = $this->baseRaw(['monto_total_clp' => 54000, 'consumo_kwh' => 0]);
        $result = $this->normalizer->normalize($raw);
        // fallback: Configuracion::get('precio_kwh_clp', 158)
        $this->assertEqualsWithDelta(158.0, $result['precio_kwh_clp'], 0.01);
    }

    public function test_precio_kwh_usa_config_si_monto_es_null(): void
    {
        $raw = $this->baseRaw(['monto_total_clp' => null, 'consumo_kwh' => 300]);
        $result = $this->normalizer->normalize($raw);
        $this->assertEqualsWithDelta(158.0, $result['precio_kwh_clp'], 0.01);
    }

    // ── region ────────────────────────────────────────────────────────────

    public function test_region_se_usa_si_claude_la_devuelve_valida(): void
    {
        $raw = $this->baseRaw(['region' => 'Valparaíso', 'comuna' => 'Valparaíso']);
        $result = $this->normalizer->normalize($raw);
        $this->assertSame('Valparaíso', $result['region']);
    }

    public function test_region_se_deduce_desde_comuna_si_claude_devuelve_invalida(): void
    {
        $raw = $this->baseRaw(['region' => 'Region Inventada', 'comuna' => 'Temuco']);
        $result = $this->normalizer->normalize($raw);
        $this->assertSame('La Araucanía', $result['region']);
    }

    public function test_region_es_null_si_no_se_puede_deducir(): void
    {
        $raw = $this->baseRaw(['region' => null, 'comuna' => 'ComunaInexistente']);
        $result = $this->normalizer->normalize($raw);
        $this->assertNull($result['region']);
    }

    // ── helper ────────────────────────────────────────────────────────────

    private function baseRaw(array $overrides = []): array
    {
        return array_merge([
            'nombre_cliente'         => 'Test Cliente',
            'rut'                    => '12.345.678-9',
            'numero_cliente'         => '123',
            'direccion'              => 'Av. Test 123',
            'comuna'                 => 'Santiago',
            'region'                 => null,
            'telefono'               => null,
            'email'                  => null,
            'potencia_contratada_kw' => null,
            'consumo_kwh'            => 300,
            'consumo_historico_kwh'  => null,
            'monto_total_clp'        => 47400,
            'tipo_tarifa'            => 'BT1',
            'distribuidora'          => 'Enel',
            'tipo_medidor'           => 'monofasico',
        ], $overrides);
    }
}
