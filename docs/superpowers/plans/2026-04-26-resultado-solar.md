# Resultado Solar — Rediseño de Página de Resultados

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transformar el step 4 de la calculadora solar en una página de conversión profesional con diseño "usted", KPIs explicados, simulación de boleta, teaser del PDF, CTA Felipe Araya por WhatsApp, correo automático al lead, y PDF enriquecido con datos constructivos.

**Architecture:** Todos los cambios viven dentro del componente Livewire `CalculadoraWizard` y sus vistas asociadas. Se añaden 3 campos económicos a `OngridCalculator`, dimensiones físicas a `GoodweCatalog`, una migración para `consentimiento`, un nuevo `Mailable` para notificar a Felipe, y se enriquece el template PDF.

**Tech Stack:** Laravel 11, Livewire v3, Blade, Tailwind CSS v4, DomPDF, Laravel Mail (queue database)

---

## Mapa de archivos

| Acción   | Archivo                                                                        | Responsabilidad                                  |
|----------|--------------------------------------------------------------------------------|--------------------------------------------------|
| Modify   | `app/Services/OngridCalculator.php`                                             | +3 campos: costo_sin_solar_clp, costo_con_solar_clp, porcentaje_reduccion |
| Modify   | `app/Services/GoodweCatalog.php`                                                | +clave `dimensiones` en cada inversor            |
| Modify   | `app/Livewire/CalculadoraWizard.php`                                            | +$consentimiento, validación, dispatch mail      |
| Create   | `database/migrations/2026_04_26_000001_add_consentimiento_to_calculadora_solicitudes_table.php` | columna booleana |
| Modify   | `app/Models/CalculadoraSolicitud.php`                                           | +consentimiento en fillable+casts                |
| Create   | `app/Mail/NuevoLeadSolarMail.php`                                               | Mailable para Felipe                             |
| Create   | `resources/views/emails/nuevo-lead-solar.blade.php`                             | HTML del correo                                  |
| Modify   | `resources/views/livewire/calculadora-wizard.blade.php`                         | Step 3: checkbox; Step 4: rediseño completo; outer bg condicional |
| Modify   | `resources/views/pdf/informe-ongrid.blade.php`                                  | +dimensiones inversor, panel, strings, proyección 25 años, contacto |
| Create   | `tests/Unit/Services/OngridCalculatorTest.php`                                  | Tests de los 3 campos nuevos                     |

---

## Task 1: OngridCalculator — campos económicos adicionales

**Files:**
- Modify: `app/Services/OngridCalculator.php` (líneas 170–195)
- Create: `tests/Unit/Services/OngridCalculatorTest.php`

- [ ] **Step 1: Escribir el test fallido**

Crear `tests/Unit/Services/OngridCalculatorTest.php`:

```php
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
```

- [ ] **Step 2: Ejecutar test para verificar que falla**

```bash
php artisan test tests/Unit/Services/OngridCalculatorTest.php --no-coverage
```
Esperado: FAIL — "Failed asserting that array has key 'costo_sin_solar_clp'"

- [ ] **Step 3: Implementar los 3 campos en OngridCalculator**

En `app/Services/OngridCalculator.php`, reemplazar el bloque de producción y economía (a partir de `$produccionMensual`) con este código (agrega las 3 variables antes del return):

```php
        // Producción y economía
        $produccionMensual = $potenciaReal * $hsp * 30 * $pr;
        $ahorroMensual     = $produccionMensual * $precioKwh;
        $costoSistema      = $nPaneles * $costoPorPanel;
        $roiAnos           = $ahorroMensual > 0 ? $costoSistema / ($ahorroMensual * 12) : 0;
        $co2KgAnual        = $produccionMensual * 12 * self::FACTOR_CO2;
        $areaM2            = $nPaneles * (($panel['largo_mm'] ?? 1722) / 1000) * (($panel['ancho_mm'] ?? 1134) / 1000);

        $costoSinSolar    = round($consumo * $precioKwh);
        $costoConSolar    = max(0, round($costoSinSolar - $ahorroMensual));
        $pctReduccion     = $costoSinSolar > 0
            ? min(100, (int) round($ahorroMensual / $costoSinSolar * 100))
            : 0;

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
            'costo_sin_solar_clp'    => $costoSinSolar,
            'costo_con_solar_clp'    => $costoConSolar,
            'porcentaje_reduccion'   => $pctReduccion,
            'roi_anos'               => round($roiAnos, 2),
            'co2_kg_anual'           => round($co2KgAnual),
            'area_m2'                => round($areaM2, 2),
            'pr'                     => round($pr, 4),
            'hsp'                    => $hsp,
            'region'                 => $region,
        ];
```

- [ ] **Step 4: Ejecutar tests para verificar que pasan**

```bash
php artisan test tests/Unit/Services/OngridCalculatorTest.php --no-coverage
```
Esperado: 4 tests, 4 passed

- [ ] **Step 5: Commit**

```bash
git add app/Services/OngridCalculator.php tests/Unit/Services/OngridCalculatorTest.php
git commit -m "feat(calculator): add costo_sin_solar, costo_con_solar, porcentaje_reduccion to OngridCalculator"
```

---

## Task 2: GoodweCatalog — dimensiones físicas de inversores

**Files:**
- Modify: `app/Services/GoodweCatalog.php`
- Modify: `app/Livewire/CalculadoraWizard.php` (panelDefault — añadir peso_kg)

- [ ] **Step 1: Añadir clave `dimensiones` a cada inversor en GoodweCatalog**

Reemplazar el contenido de `app/Services/GoodweCatalog.php` completamente con la versión que incluye `dimensiones`. Los grupos de dimensiones son:
- DNS G4 (todos): Alto 304 mm, Ancho 256 mm, Fondo 122 mm, Peso 7.3 kg
- SDT G2 PLUS+ 4–10 kW: Alto 370 mm, Ancho 310 mm, Fondo 148 mm, Peso 10.5 kg
- SDT G2 PLUS+ 12–20 kW: Alto 417 mm, Ancho 480 mm, Fondo 182 mm, Peso 18.5 kg
- SDT G3 10–12 kW: Alto 418 mm, Ancho 400 mm, Fondo 174 mm, Peso 20 kg
- SDT G3 15–20 kW: Alto 500 mm, Ancho 480 mm, Fondo 195 mm, Peso 25 kg

```php
<?php
namespace App\Services;

class GoodweCatalog
{
    public static function inversores(): array
    {
        return [
            // ── Monofásico: DNS G4 (3–6 kW, 2 MPPT) ──────────────────────
            [
                'id' => 'GW3K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW3K-DNS-G40',
                'potencia_kw' => 3.0, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 6.0,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW3.6K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW3.6K-DNS-G40',
                'potencia_kw' => 3.6, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 7.2,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW4.2K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW4.2K-DNS-G40',
                'potencia_kw' => 4.2, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 8.4,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW5K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW5K-DNS-G40',
                'potencia_kw' => 5.0, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 10.0,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],
            [
                'id' => 'GW6K-DNS-G40', 'marca' => 'GoodWe', 'modelo' => 'GW6K-DNS-G40',
                'potencia_kw' => 6.0, 'fases' => 'monofasico', 'num_mppt' => 2,
                'v_mppt_min' => 40, 'v_mppt_max' => 560,
                'corriente_max_dc' => 20, 'potencia_max_dc_kw' => 12.0,
                'eficiencia_max_pct' => 98.1, 'activo' => true,
                'dimensiones' => ['alto_mm' => 304, 'ancho_mm' => 256, 'fondo_mm' => 122, 'peso_kg' => 7.3],
            ],

            // ── Trifásico: SDT G2 PLUS+ (4–20 kW, 2 MPPT) ────────────────
            [
                'id' => 'GW4K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW4K-SDT-20',
                'potencia_kw' => 4.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 12.5, 'potencia_max_dc_kw' => 6.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW5K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW5K-SDT-20',
                'potencia_kw' => 5.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 12.5, 'potencia_max_dc_kw' => 7.5,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW6K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW6K-SDT-20',
                'potencia_kw' => 6.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 12.5, 'potencia_max_dc_kw' => 9.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW8K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW8K-SDT-20',
                'potencia_kw' => 8.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 12.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW10K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW10K-SDT-20',
                'potencia_kw' => 10.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 15.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 370, 'ancho_mm' => 310, 'fondo_mm' => 148, 'peso_kg' => 10.5],
            ],
            [
                'id' => 'GW12K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW12K-SDT-20',
                'potencia_kw' => 12.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 18.0,
                'eficiencia_max_pct' => 98.3, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],
            [
                'id' => 'GW15K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW15K-SDT-20',
                'potencia_kw' => 15.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 22.5,
                'eficiencia_max_pct' => 98.4, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],
            [
                'id' => 'GW17K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW17K-SDT-20',
                'potencia_kw' => 17.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 25.5,
                'eficiencia_max_pct' => 98.4, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],
            [
                'id' => 'GW20K-SDT-20', 'marca' => 'GoodWe', 'modelo' => 'GW20K-SDT-20',
                'potencia_kw' => 20.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 180, 'v_mppt_max' => 850,
                'corriente_max_dc' => 15.0, 'potencia_max_dc_kw' => 30.0,
                'eficiencia_max_pct' => 98.4, 'activo' => true,
                'dimensiones' => ['alto_mm' => 417, 'ancho_mm' => 480, 'fondo_mm' => 182, 'peso_kg' => 18.5],
            ],

            // ── Trifásico: SDT G3 (10–20 kW, 2 MPPT) — con SEC Chile ─────
            [
                'id' => 'GW10K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW10K-SDT-30',
                'potencia_kw' => 10.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 15.0,
                'eficiencia_max_pct' => 98.6, 'activo' => true,
                'dimensiones' => ['alto_mm' => 418, 'ancho_mm' => 400, 'fondo_mm' => 174, 'peso_kg' => 20],
            ],
            [
                'id' => 'GW12K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW12K-SDT-30',
                'potencia_kw' => 12.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 18.0,
                'eficiencia_max_pct' => 98.6, 'activo' => true,
                'dimensiones' => ['alto_mm' => 418, 'ancho_mm' => 400, 'fondo_mm' => 174, 'peso_kg' => 20],
            ],
            [
                'id' => 'GW15K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW15K-SDT-30',
                'potencia_kw' => 15.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 22.5,
                'eficiencia_max_pct' => 98.6, 'activo' => true,
                'dimensiones' => ['alto_mm' => 500, 'ancho_mm' => 480, 'fondo_mm' => 195, 'peso_kg' => 25],
            ],
            [
                'id' => 'GW17K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW17K-SDT-30',
                'potencia_kw' => 17.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 25.5,
                'eficiencia_max_pct' => 98.7, 'activo' => true,
                'dimensiones' => ['alto_mm' => 500, 'ancho_mm' => 480, 'fondo_mm' => 195, 'peso_kg' => 25],
            ],
            [
                'id' => 'GW20K-SDT-30', 'marca' => 'GoodWe', 'modelo' => 'GW20K-SDT-30',
                'potencia_kw' => 20.0, 'fases' => 'trifasico', 'num_mppt' => 2,
                'v_mppt_min' => 140, 'v_mppt_max' => 1000,
                'corriente_max_dc' => 22.0, 'potencia_max_dc_kw' => 30.0,
                'eficiencia_max_pct' => 98.7, 'activo' => true,
                'dimensiones' => ['alto_mm' => 500, 'ancho_mm' => 480, 'fondo_mm' => 195, 'peso_kg' => 25],
            ],
        ];
    }
}
```

- [ ] **Step 2: Añadir `peso_kg` al panel en `panelDefault()` de CalculadoraWizard**

En `app/Livewire/CalculadoraWizard.php`, en `panelDefault()`, añadir `'peso_kg' => 21.3` al array:

```php
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
```

- [ ] **Step 3: Ejecutar tests existentes para verificar no hay regresión**

```bash
php artisan test tests/Unit/ --no-coverage
```
Esperado: todos los tests anteriores siguen pasando

- [ ] **Step 4: Commit**

```bash
git add app/Services/GoodweCatalog.php app/Livewire/CalculadoraWizard.php
git commit -m "feat(catalog): add physical dimensions to GoodWe inverters and panel weight"
```

---

## Task 3: Migración — columna `consentimiento`

**Files:**
- Create: `database/migrations/2026_04_26_000001_add_consentimiento_to_calculadora_solicitudes_table.php`
- Modify: `app/Models/CalculadoraSolicitud.php`

- [ ] **Step 1: Crear la migración**

Crear `database/migrations/2026_04_26_000001_add_consentimiento_to_calculadora_solicitudes_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculadora_solicitudes', function (Blueprint $table) {
            $table->boolean('consentimiento')->default(false)->after('empresa');
        });
    }

    public function down(): void
    {
        Schema::table('calculadora_solicitudes', function (Blueprint $table) {
            $table->dropColumn('consentimiento');
        });
    }
};
```

- [ ] **Step 2: Actualizar el modelo**

En `app/Models/CalculadoraSolicitud.php`, actualizar `$fillable` y `$casts`:

```php
    protected $fillable = [
        'uuid', 'nombre', 'email', 'telefono', 'empresa',
        'consentimiento', 'datos_boleta', 'resultado', 'pdf_path', 'estado',
    ];
    protected $casts = [
        'datos_boleta'  => 'array',
        'resultado'     => 'array',
        'consentimiento' => 'boolean',
    ];
```

- [ ] **Step 3: Ejecutar la migración**

```bash
php artisan migrate
```
Esperado: `Running migrations. 2026_04_26_000001_add_consentimiento... DONE`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_04_26_000001_add_consentimiento_to_calculadora_solicitudes_table.php app/Models/CalculadoraSolicitud.php
git commit -m "feat(db): add consentimiento column to calculadora_solicitudes"
```

---

## Task 4: Consentimiento en CalculadoraWizard + Blade step 3

**Files:**
- Modify: `app/Livewire/CalculadoraWizard.php`
- Modify: `resources/views/livewire/calculadora-wizard.blade.php` (step 3, líneas ~209–224)

- [ ] **Step 1: Añadir `$consentimiento` y validación en CalculadoraWizard**

En `app/Livewire/CalculadoraWizard.php`:

1. Añadir la propiedad después de `public string $error = '';`:
```php
    public bool $consentimiento = false;
```

2. En `confirmarDatos()`, añadir al array de `$this->validate`:
```php
            'consentimiento'             => 'accepted',
```

3. En `confirmarDatos()`, añadir en el array del `->update()`:
```php
            'consentimiento' => $this->consentimiento,
```

4. En `reiniciar()`, añadir reset:
```php
        $this->consentimiento = false;
```

El método `confirmarDatos()` completo queda:

```php
    public function confirmarDatos(): void
    {
        $this->validate([
            'datosBoleta.nombre_cliente' => 'required|string|max:100',
            'datosBoleta.region'         => 'required|string',
            'datosBoleta.telefono'       => 'required|string|max:20',
            'datosBoleta.email'          => 'required|email:rfc|max:150',
            'consentimiento'             => 'accepted',
        ]);

        try {
            $calc            = app(OngridCalculator::class);
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

        $solicitud = CalculadoraSolicitud::find($this->solicitudId);
        $solicitud?->update([
            'nombre'         => $this->datosBoleta['nombre_cliente'] ?? '',
            'email'          => $this->datosBoleta['email'] ?? '',
            'telefono'       => $this->datosBoleta['telefono'] ?? '',
            'empresa'        => $this->datosBoleta['empresa'] ?? '',
            'consentimiento' => $this->consentimiento,
            'datos_boleta'   => $this->datosBoleta,
            'resultado'      => $this->resultado,
        ]);

        $this->step = 4;
    }
```

- [ ] **Step 2: Añadir checkbox de consentimiento en step 3 del blade**

En `resources/views/livewire/calculadora-wizard.blade.php`, insertar antes del botón "Ver mi resultado solar" (antes de la línea que contiene `wire:click="confirmarDatos"`):

```blade
            {{-- Consentimiento --}}
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" wire:model="consentimiento"
                    class="mt-0.5 w-4 h-4 accent-[#0067FF] shrink-0">
                <span class="text-white/60 text-xs leading-relaxed">
                    Autorizo a Enertecs SpA a utilizar mis datos de contacto para hacerme llegar información sobre mi proyecto solar. *
                </span>
            </label>
            @error('consentimiento')
                <p class="text-red-400 text-xs">Debe aceptar el uso de sus datos para continuar.</p>
            @enderror
```

- [ ] **Step 3: Verificar manualmente que el checkbox aparece y que la validación funciona**

Navegar a `/calculadora/solar-ongrid`, subir un PDF, llegar al step 3 y verificar:
- El checkbox aparece al final del formulario antes del botón
- Si se intenta avanzar sin marcarlo, aparece el error "Debe aceptar el uso de sus datos para continuar."

- [ ] **Step 4: Commit**

```bash
git add app/Livewire/CalculadoraWizard.php resources/views/livewire/calculadora-wizard.blade.php
git commit -m "feat(wizard): add consentimiento checkbox to step 3 with validation"
```

---

## Task 5: Mailable NuevoLeadSolarMail + email HTML

**Files:**
- Create: `app/Mail/NuevoLeadSolarMail.php`
- Create: `resources/views/emails/nuevo-lead-solar.blade.php`
- Modify: `app/Livewire/CalculadoraWizard.php` (dispatch del correo)

- [ ] **Step 1: Crear el Mailable**

Crear `app/Mail/NuevoLeadSolarMail.php`:

```php
<?php

namespace App\Mail;

use App\Models\CalculadoraSolicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoLeadSolarMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly CalculadoraSolicitud $solicitud) {}

    public function envelope(): Envelope
    {
        $nombre = $this->solicitud->nombre ?? 'Cliente';
        $ahorro = number_format($this->solicitud->resultado['ahorro_mensual_clp'] ?? 0, 0, ',', '.');
        return new Envelope(
            subject: "Nuevo lead solar — {$nombre} (\${$ahorro}/mes)",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.nuevo-lead-solar');
    }
}
```

- [ ] **Step 2: Crear el directorio y template del correo**

Crear `resources/views/emails/nuevo-lead-solar.blade.php`:

```blade
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; margin: 0; padding: 20px; }
.card { background: white; border-radius: 12px; padding: 24px; max-width: 560px; margin: 0 auto; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.header { background: #0067FF; color: white; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 18px; }
.header p  { margin: 4px 0 0; font-size: 12px; opacity: 0.8; }
.kpi { background: #eff6ff; border-left: 4px solid #0067FF; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
.kpi .valor { font-size: 28px; font-weight: 900; color: #0067FF; }
.kpi .label { font-size: 12px; color: #64748b; }
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
td { padding: 6px 0; font-size: 13px; }
td:first-child { color: #64748b; width: 40%; }
td:last-child { font-weight: 600; }
.footer { font-size: 11px; color: #94a3b8; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 12px; }
.nota { background: #fef9c3; border: 1px solid #fde047; border-radius: 6px; padding: 10px 14px; font-size: 12px; color: #713f12; margin-bottom: 16px; }
</style>
</head>
<body>
<div class="card">

  <div class="header">
    <h1>Nuevo lead solar — Enertecs SpA</h1>
    <p>Análisis completado el {{ now()->format('d/m/Y H:i') }}</p>
  </div>

  @php
    $r = $solicitud->resultado ?? [];
    $d = $solicitud->datos_boleta ?? [];
    $ahorro = $r['ahorro_mensual_clp'] ?? 0;
  @endphp

  <div class="kpi">
    <div class="valor">${{ number_format($ahorro, 0, ',', '.') }}/mes</div>
    <div class="label">Ahorro mensual estimado con solar</div>
  </div>

  <p style="font-size:13px;font-weight:700;color:#374151;margin:0 0 8px">Datos del cliente</p>
  <table>
    <tr><td>Nombre</td><td>{{ $solicitud->nombre ?? '—' }}</td></tr>
    <tr><td>Teléfono</td><td>{{ $solicitud->telefono ?? '—' }}</td></tr>
    <tr><td>Email</td><td>{{ $solicitud->email ?? '—' }}</td></tr>
    <tr><td>Distribuidora</td><td>{{ $d['distribuidora'] ?? '—' }}</td></tr>
    <tr><td>Región</td><td>{{ $d['region'] ?? '—' }}</td></tr>
  </table>

  <p style="font-size:13px;font-weight:700;color:#374151;margin:0 0 8px">Sistema dimensionado</p>
  <table>
    <tr><td>Paneles</td><td>{{ $r['n_paneles'] ?? '—' }} × {{ ($r['panel']['marca'] ?? '') }} {{ ($r['panel']['modelo'] ?? '') }} {{ ($r['panel']['potencia_wp'] ?? '') }}Wp</td></tr>
    <tr>
      <td>Inversor</td>
      <td>
        @if(($r['n_inversores'] ?? 1) > 1){{ $r['n_inversores'] }} × @endif{{ $r['inversor']['modelo'] ?? '—' }}
      </td>
    </tr>
    <tr><td>Potencia total</td><td>{{ number_format($r['potencia_real_kwp'] ?? 0, 2) }} kWp</td></tr>
    <tr><td>ROI estimado</td><td>{{ number_format($r['roi_anos'] ?? 0, 1) }} años</td></tr>
    <tr><td>Reducción boleta</td><td>{{ $r['porcentaje_reduccion'] ?? '—' }}%</td></tr>
  </table>

  <div class="nota">
    ✅ El cliente autorizó el uso de sus datos de contacto para recibir información sobre su proyecto solar.
  </div>

  <div class="footer">
    Enertecs SpA · este correo fue generado automáticamente por el sistema de análisis solar con IA.
  </div>
</div>
</body>
</html>
```

- [ ] **Step 3: Añadir los imports y dispatch en CalculadoraWizard**

En `app/Livewire/CalculadoraWizard.php`, añadir al bloque de `use` al inicio:

```php
use App\Mail\NuevoLeadSolarMail;
use Illuminate\Support\Facades\Mail;
```

En `confirmarDatos()`, después de `$this->step = 4;`, añadir:

```php
        if ($solicitud) {
            Mail::to('felipe.araya@enertecs.cl')->queue(new NuevoLeadSolarMail($solicitud));
        }
```

- [ ] **Step 4: Verificar que el correo se encola (no hay errores)**

```bash
php artisan queue:work --once 2>&1 | head -5
```
Esperado: sin errores de clase no encontrada. Si hay un job de correo pendiente, lo procesa y no lanza excepciones.

- [ ] **Step 5: Commit**

```bash
git add app/Mail/NuevoLeadSolarMail.php resources/views/emails/nuevo-lead-solar.blade.php app/Livewire/CalculadoraWizard.php
git commit -m "feat(mail): send NuevoLeadSolarMail to Felipe on step 4 completion"
```

---

## Task 6: Rediseño del Step 4 — página de resultados

**Files:**
- Modify: `resources/views/livewire/calculadora-wizard.blade.php` (outer div + step 4)

- [ ] **Step 1: Cambiar el fondo del outer div a condicional**

En `resources/views/livewire/calculadora-wizard.blade.php`, reemplazar la primera línea:

```blade
<div class="min-h-screen bg-[#0a1628]">
```

por:

```blade
<div @class(['min-h-screen', 'bg-[#0a1628]' => $step !== 4, 'bg-[#f0f4f8]' => $step === 4])>
```

- [ ] **Step 2: Reemplazar el bloque Step 4 completo**

En `resources/views/livewire/calculadora-wizard.blade.php`, reemplazar desde `{{-- Step 4: Resultado --}}` hasta el cierre `@endif` (líneas 229–263) con:

```blade
    {{-- Step 4: Resultado --}}
    @if($step === 4)
    @php
        $nombre        = $datosBoleta['nombre_cliente'] ?? 'Cliente';
        $distribuidora = $datosBoleta['distribuidora'] ?? 'su distribuidora';
        $ahorro        = $resultado['ahorro_mensual_clp'] ?? 0;
        $roi           = $resultado['roi_anos'] ?? 0;
        $pctReduccion  = $resultado['porcentaje_reduccion'] ?? 0;
        $costoSin      = $resultado['costo_sin_solar_clp'] ?? 0;
        $costoConSolar = $resultado['costo_con_solar_clp'] ?? 0;
        $barraSolar    = $costoSin > 0 ? max(4, (int) round($costoConSolar / $costoSin * 64)) : 4;
        $nPaneles      = $resultado['n_paneles'] ?? 0;
        $panel         = $resultado['panel'] ?? [];
        $inversor      = $resultado['inversor'] ?? [];
        $nInversores   = $resultado['n_inversores'] ?? 1;
        $kwp           = $resultado['potencia_real_kwp'] ?? 0;
        $areaM2        = $resultado['area_m2'] ?? 0;
        $co2           = $resultado['co2_kg_anual'] ?? 0;
        $waMsg         = rawurlencode("Hola Felipe, soy {$nombre} y acabo de ver mi análisis solar en Enertecs. Me gustaría conocer más sobre instalar paneles en mi hogar.");
        $waUrl         = "https://wa.me/56935165830?text={$waMsg}";
    @endphp
    <div class="max-w-md mx-auto pb-10">

        {{-- Encabezado azul --}}
        <div class="bg-[#0067FF] px-6 pt-5 pb-8">
            <div class="text-white/65 text-[11px] mb-1.5">Enertecs SpA · Análisis solar con IA</div>
            <h1 class="text-white text-xl font-black leading-tight mb-1">Buenas noticias, {{ $nombre }}.</h1>
            <p class="text-white/75 text-xs">Preparamos su estimación solar en base a su boleta de {{ $distribuidora }}.</p>
        </div>

        {{-- Hero KPI flotante --}}
        <div class="mx-4 -mt-3 bg-white rounded-2xl p-5 shadow-xl">
            <p class="text-slate-500 text-[11px] mb-1">Si instala un sistema solar hoy, usted podría ahorrar</p>
            <div class="text-4xl font-black text-[#0067FF] leading-none">${{ number_format($ahorro, 0, ',', '.') }}</div>
            <p class="text-slate-500 text-[11px] mt-1">al mes — <strong class="text-green-600">desde el primer día de operación</strong></p>
        </div>

        <div class="px-4 pt-4 space-y-4">

            {{-- 3 KPIs --}}
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-white rounded-xl p-3 shadow-sm">
                    <div class="text-lg font-black text-[#0067FF]">${{ number_format($ahorro, 0, ',', '.') }}</div>
                    <div class="text-[9px] font-bold text-gray-700 mt-0.5">Ahorro mensual</div>
                    <div class="text-[8px] text-gray-400 leading-tight mt-0.5">Lo que deja de pagarle a la distribuidora cada mes</div>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm">
                    <div class="text-lg font-black text-emerald-600">{{ number_format($roi, 1) }} a.</div>
                    <div class="text-[9px] font-bold text-gray-700 mt-0.5">Recupera su inversión</div>
                    <div class="text-[8px] text-gray-400 leading-tight mt-0.5">Después de ese plazo, la energía solar es sin costo</div>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm">
                    <div class="text-lg font-black text-violet-600">−{{ $pctReduccion }}%</div>
                    <div class="text-[9px] font-bold text-gray-700 mt-0.5">Baja su boleta</div>
                    <div class="text-[8px] text-gray-400 leading-tight mt-0.5">De ${{ number_format($costoSin, 0, ',', '.') }} a ~${{ number_format($costoConSolar, 0, ',', '.') }} al mes</div>
                </div>
            </div>

            {{-- Simulación de boleta --}}
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-[11px] font-bold text-gray-700 mb-3">Así se vería su boleta</p>
                <div class="flex items-end gap-4">
                    <div class="flex-1 text-center">
                        <div class="text-[10px] text-gray-500 mb-1">Hoy</div>
                        <div class="bg-red-100 rounded-t-lg h-16 flex items-center justify-center">
                            <span class="text-red-600 text-[13px] font-black">${{ number_format($costoSin, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-red-300 h-0.5 rounded-b"></div>
                    </div>
                    <div class="text-gray-300 text-xl pb-5">→</div>
                    <div class="flex-1 text-center">
                        <div class="text-[10px] text-gray-500 mb-1">Con solar</div>
                        <div class="bg-green-100 rounded-t-lg" style="height: {{ $barraSolar }}px;"></div>
                        <div class="bg-green-300 h-0.5 rounded-b"></div>
                        <div class="text-[13px] font-black text-green-600 mt-1.5">~${{ number_format($costoConSolar, 0, ',', '.') }}</div>
                    </div>
                </div>
                <p class="text-[8px] text-gray-400 text-center mt-2.5">Estimación basada en su consumo histórico y el precio real de su tarifa</p>
            </div>

            {{-- Su sistema solar --}}
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-[11px] font-bold text-gray-700 mb-2.5">Su sistema solar</p>
                <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-[9px]">
                    <span class="text-gray-400">Paneles</span>
                    <span class="text-gray-700 font-semibold">{{ $nPaneles }} × {{ ($panel['marca'] ?? '') }} {{ ($panel['modelo'] ?? '') }} {{ ($panel['potencia_wp'] ?? '') }} Wp</span>
                    <span class="text-gray-400">Inversor</span>
                    <span class="text-gray-700 font-semibold">@if($nInversores > 1){{ $nInversores }} × @endif{{ $inversor['modelo'] ?? '—' }}</span>
                    <span class="text-gray-400">Potencia total</span>
                    <span class="text-gray-700 font-semibold">{{ number_format($kwp, 2) }} kWp</span>
                    <span class="text-gray-400">Área de techo</span>
                    <span class="text-gray-700 font-semibold">~{{ number_format($areaM2, 1) }} m²</span>
                    <span class="text-gray-400">CO₂ que evita</span>
                    <span class="text-green-600 font-semibold">{{ number_format($co2) }} kg al año</span>
                </div>
            </div>

            {{-- Teaser PDF --}}
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-[#0067FF]">
                <p class="text-[11px] font-bold text-gray-700 mb-1">Su informe completo incluye además</p>
                <p class="text-[9px] text-slate-500 mb-2.5">Descargue el PDF para ver el detalle técnico y constructivo de su proyecto.</p>
                <ul class="text-[9px] text-slate-600 leading-loose list-disc pl-3.5">
                    <li>Dimensiones físicas del inversor (para la instalación)</li>
                    <li>Metros cuadrados exactos que necesita en su techo</li>
                    <li>Configuración eléctrica: strings y cableado</li>
                    <li>Proyección de ahorro a 25 años</li>
                    <li>Ficha técnica completa del panel solar</li>
                    <li>Datos de contacto de Enertecs SpA</li>
                </ul>
                @if($solicitudUuid)
                <a href="{{ route('calculadora.informe', $solicitudUuid) }}"
                   class="mt-3 flex items-center justify-center gap-2 bg-[#0067FF] hover:bg-[#0050CC] text-white text-[10px] font-bold py-2.5 rounded-xl transition-colors">
                    Descargar informe PDF
                </a>
                @endif
            </div>

            {{-- CTA Felipe Araya --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                <p class="text-sm font-bold text-blue-800 mb-0.5">¿Le interesa avanzar?</p>
                <p class="text-[10px] text-blue-500 mb-3">Nuestro ingeniero lo asesora sin compromiso.</p>
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-10 h-10 bg-[#0067FF] rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0">FA</div>
                    <div>
                        <div class="text-sm font-bold text-slate-800">Felipe Araya</div>
                        <div class="text-[10px] text-slate-500">Ingeniero de Desarrollo de Negocio</div>
                    </div>
                </div>
                <a href="{{ $waUrl }}"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1fbe5a] text-white text-[11px] font-bold py-3 rounded-xl transition-colors">
                    Escribirle por WhatsApp
                </a>
                <p class="text-[9px] text-slate-400 text-center mt-1.5">+56 9 3516 5830</p>
            </div>

            {{-- Nueva consulta --}}
            <button wire:click="reiniciar"
                    class="w-full bg-white border border-slate-200 rounded-xl py-3 text-slate-400 text-xs hover:text-slate-600 transition-colors">
                Nueva consulta
            </button>

        </div>
    </div>
    @endif
```

- [ ] **Step 3: Verificar que el background del wizard cambia a claro en step 4**

En el browser, completar el flujo hasta step 4 y confirmar:
- El fondo es claro (`#f0f4f8`) en lugar del dark `#0a1628`
- Los steps 1–3 siguen con fondo oscuro
- El encabezado azul, las KPI cards blancas, la simulación de boleta, el teaser y el CTA aparecen correctamente

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/calculadora-wizard.blade.php
git commit -m "feat(wizard): redesign step 4 results page with light bg, KPIs, bill simulation, PDF teaser, Felipe CTA"
```

---

## Task 7: PDF enriquecido con datos constructivos

**Files:**
- Modify: `resources/views/pdf/informe-ongrid.blade.php`

- [ ] **Step 1: Reemplazar el template PDF completo**

Reemplazar el contenido de `resources/views/pdf/informe-ongrid.blade.php` con:

```blade
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; margin: 0; padding: 20px; }
  .header { background: #0067FF; color: white; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
  .header h1 { margin: 0; font-size: 18px; }
  .header p  { margin: 4px 0 0; font-size: 11px; opacity: 0.8; }
  .section { margin-bottom: 18px; }
  .section h2 { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #0067FF;
                border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; }
  td { padding: 4px 8px; vertical-align: top; }
  td:first-child { color: #6b7280; width: 45%; }
  td:last-child { font-weight: bold; }
  .highlight { background: #eff6ff; border-left: 3px solid #0067FF; padding: 8px 12px; margin: 12px 0; border-radius: 2px; }
  .highlight td:first-child { color: #3b82f6; }
  .proyeccion-table { width: 100%; border-collapse: collapse; font-size: 10px; }
  .proyeccion-table th { background: #0067FF; color: white; padding: 5px 8px; text-align: left; }
  .proyeccion-table td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; font-weight: normal; }
  .proyeccion-table td:first-child { color: #6b7280; }
  .proyeccion-table td:last-child { font-weight: bold; color: #059669; }
  .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px;
            font-size: 9px; color: #9ca3af; text-align: center; }
  .contact-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 4px; padding: 12px 16px; }
  .contact-box strong { color: #0067FF; }
  .nota { font-size: 9px; color: #9ca3af; margin-top: 6px; }
</style>
</head>
<body>

<div class="header">
  <h1>Informe Solar OnGrid — Enertecs SpA</h1>
  <p>Fecha: {{ now()->format('d/m/Y') }} · Folio: #{{ $solicitud->id }}</p>
</div>

@php
  $d = $solicitud->datos_boleta ?? [];
  $r = $solicitud->resultado ?? [];
  $inv = $r['inversor'] ?? [];
  $pan = $r['panel'] ?? [];
  $dimInv = $inv['dimensiones'] ?? [];
  $nInv = $r['n_inversores'] ?? 1;

  // Proyección 25 años
  $prodMensual = $r['produccion_mensual_kwh'] ?? 0;
  $precioBase  = (float) ($d['precio_kwh_clp'] ?? 158);
  $proyAnios   = [1, 5, 10, 15, 20, 25];
  $proyeccion  = [];
  $acumulado   = 0;
  for ($y = 1; $y <= 25; $y++) {
      $prodYear   = $prodMensual * 12 * pow(0.995, $y);
      $precioYear = $precioBase * pow(1.03, $y);
      $ahorroYear = round($prodYear * $precioYear);
      $acumulado += $ahorroYear;
      if (in_array($y, $proyAnios)) {
          $proyeccion[$y] = ['ahorro_anual' => $ahorroYear, 'acumulado' => $acumulado];
      }
  }
@endphp

<div class="section">
  <h2>Datos del cliente</h2>
  <table>
    <tr><td>Nombre</td><td>{{ $d['nombre_cliente'] ?? $solicitud->nombre ?? '—' }}</td></tr>
    <tr><td>RUT</td><td>{{ $d['rut'] ?? '—' }}</td></tr>
    <tr><td>Dirección</td><td>{{ $d['direccion'] ?? '—' }}</td></tr>
    <tr><td>Región</td><td>{{ $d['region'] ?? '—' }}</td></tr>
    <tr><td>Distribuidora</td><td>{{ $d['distribuidora'] ?? '—' }}</td></tr>
    <tr><td>Tarifa</td><td>{{ $d['tipo_tarifa'] ?? '—' }}</td></tr>
    <tr><td>Consumo mensual</td><td>{{ $d['consumo_kwh'] ?? '—' }} kWh</td></tr>
  </table>
</div>

<div class="section">
  <h2>Sistema dimensionado</h2>
  <table>
    <tr><td>Potencia del sistema</td><td>{{ number_format($r['potencia_real_kwp'] ?? 0, 2) }} kWp</td></tr>
    <tr><td>Cantidad de paneles</td><td>{{ $r['n_paneles'] ?? '—' }}</td></tr>
    <tr><td>Panel seleccionado</td><td>{{ ($pan['marca'] ?? '') }} {{ ($pan['modelo'] ?? '') }} {{ ($pan['potencia_wp'] ?? '') }} Wp</td></tr>
    <tr>
      <td>Inversor seleccionado</td>
      <td>@if($nInv > 1){{ $nInv }} × @endif{{ $inv['modelo'] ?? '—' }}</td>
    </tr>
    <tr><td>Área requerida en techo</td><td>{{ number_format($r['area_m2'] ?? 0, 1) }} m²</td></tr>
    <tr><td>HSP de la región</td><td>{{ $r['hsp'] ?? '—' }} h/día</td></tr>
    <tr><td>Strings totales</td><td>{{ $r['n_strings'] ?? '—' }}</td></tr>
    <tr><td>Paneles por string</td><td>{{ $r['paneles_por_string'] ?? '—' }}</td></tr>
  </table>
</div>

<div class="section">
  <h2>Proyección económica</h2>
  <div class="highlight">
    <table>
      <tr><td>Producción mensual estimada</td><td>{{ number_format($r['produccion_mensual_kwh'] ?? 0) }} kWh/mes</td></tr>
      <tr><td>Ahorro mensual estimado</td><td>${{ number_format($r['ahorro_mensual_clp'] ?? 0, 0, ',', '.') }} CLP</td></tr>
      <tr><td>Reducción de boleta</td><td>{{ $r['porcentaje_reduccion'] ?? '—' }}%</td></tr>
      <tr><td>Boleta con solar</td><td>~${{ number_format($r['costo_con_solar_clp'] ?? 0, 0, ',', '.') }} CLP/mes</td></tr>
      <tr><td>Retorno de inversión</td><td>{{ number_format($r['roi_anos'] ?? 0, 1) }} años</td></tr>
      <tr><td>CO₂ evitado anualmente</td><td>{{ number_format($r['co2_kg_anual'] ?? 0) }} kg</td></tr>
    </table>
  </div>
</div>

<div class="section">
  <h2>Proyección de ahorro a 25 años</h2>
  <table class="proyeccion-table">
    <thead>
      <tr>
        <th>Año</th>
        <th>Ahorro anual estimado</th>
        <th>Ahorro acumulado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($proyeccion as $anio => $datos)
      <tr>
        <td>Año {{ $anio }}</td>
        <td>${{ number_format($datos['ahorro_anual'], 0, ',', '.') }}</td>
        <td>${{ number_format($datos['acumulado'], 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <p class="nota">Supone degradación del panel 0,5%/año y alza de tarifa eléctrica 3%/año.</p>
</div>

<div class="section">
  <h2>Ficha técnica del panel solar</h2>
  <table>
    <tr><td>Marca y modelo</td><td>{{ ($pan['marca'] ?? '') }} {{ ($pan['modelo'] ?? '') }}</td></tr>
    <tr><td>Potencia pico</td><td>{{ $pan['potencia_wp'] ?? '—' }} Wp</td></tr>
    <tr><td>Eficiencia</td><td>{{ $pan['eficiencia_pct'] ?? '—' }}%</td></tr>
    <tr><td>Tipo</td><td>{{ ucfirst($pan['tipo'] ?? '—') }}</td></tr>
    <tr><td>Dimensiones</td><td>{{ ($pan['largo_mm'] ?? '—') }} × {{ ($pan['ancho_mm'] ?? '—') }} × {{ ($pan['alto_mm'] ?? '—') }} mm</td></tr>
    <tr><td>Peso</td><td>{{ $pan['peso_kg'] ?? '—' }} kg</td></tr>
    <tr><td>Tensión circuito abierto (Voc)</td><td>{{ $pan['v_oc'] ?? '—' }} V</td></tr>
    <tr><td>Tensión punto máximo (Vmpp)</td><td>{{ $pan['v_mpp'] ?? '—' }} V</td></tr>
    <tr><td>Corriente cortocircuito (Isc)</td><td>{{ $pan['i_sc'] ?? '—' }} A</td></tr>
    <tr><td>Corriente punto máximo (Impp)</td><td>{{ $pan['i_mpp'] ?? '—' }} A</td></tr>
    <tr><td>Certificación SEC Chile</td><td>{{ ($pan['certificacion_sec'] ?? false) ? 'Sí' : 'No' }}</td></tr>
  </table>
</div>

<div class="section">
  <h2>Datos constructivos del inversor</h2>
  <table>
    <tr><td>Marca y modelo</td><td>@if($nInv > 1){{ $nInv }} × @endif{{ $inv['modelo'] ?? '—' }}</td></tr>
    <tr><td>Potencia nominal AC</td><td>{{ $inv['potencia_kw'] ?? '—' }} kW</td></tr>
    <tr><td>Fases</td><td>{{ ucfirst($inv['fases'] ?? '—') }}</td></tr>
    <tr><td>N.° de entradas MPPT</td><td>{{ $inv['num_mppt'] ?? '—' }}</td></tr>
    <tr><td>Eficiencia máxima</td><td>{{ $inv['eficiencia_max_pct'] ?? '—' }}%</td></tr>
    @if(!empty($dimInv))
    <tr><td>Dimensiones (Alto × Ancho × Fondo)</td><td>{{ $dimInv['alto_mm'] }} × {{ $dimInv['ancho_mm'] }} × {{ $dimInv['fondo_mm'] }} mm</td></tr>
    <tr><td>Peso</td><td>{{ $dimInv['peso_kg'] }} kg</td></tr>
    @endif
    <tr><td>Strings totales</td><td>{{ $r['n_strings'] ?? '—' }}</td></tr>
    <tr><td>Paneles por string</td><td>{{ $r['paneles_por_string'] ?? '—' }}</td></tr>
  </table>
</div>

<div class="section">
  <h2>Contacto Enertecs SpA</h2>
  <div class="contact-box">
    <strong>Felipe Araya</strong> — Ingeniero de Desarrollo de Negocio<br>
    WhatsApp: +56 9 3516 5830<br>
    Email: felipe.araya@enertecs.cl<br>
    Web: enertecs.cl
  </div>
</div>

<div class="footer">
  Enertecs SpA · Ingeniería Eléctrica · Punta Arenas, Chile<br>
  Este informe es una estimación referencial. Los valores reales pueden variar según las condiciones del sitio de instalación.
</div>

</body>
</html>
```

- [ ] **Step 2: Verificar el PDF en el browser**

Completar el flujo del wizard hasta step 4 y hacer clic en "Descargar informe PDF". Verificar que el PDF incluye:
- Sección "Proyección de ahorro a 25 años" con tabla de 6 filas (años 1, 5, 10, 15, 20, 25)
- Sección "Ficha técnica del panel solar" con todos los campos
- Sección "Datos constructivos del inversor" con dimensiones en mm y peso en kg
- Sección "Contacto Enertecs SpA" con Felipe Araya al final

- [ ] **Step 3: Commit**

```bash
git add resources/views/pdf/informe-ongrid.blade.php
git commit -m "feat(pdf): add 25-year projection, panel specs, inverter dimensions, contact section"
```

---

## Verificación final

- [ ] Ejecutar toda la suite de tests

```bash
php artisan test --no-coverage
```
Esperado: todos pasan, sin errores.

- [ ] Flujo completo end-to-end

1. `/calculadora/solar-ongrid` → subir un PDF real → polling → step 3
2. En step 3: completar todos los campos, verificar que el checkbox aparece
3. Intentar avanzar sin checkbox → error de validación
4. Marcar checkbox → "Ver mi resultado solar"
5. Step 4: fondo claro, encabezado azul, KPI flotante, 3 cards, simulación boleta, teaser, Felipe CTA, botón PDF
6. Clic "Descargar informe PDF" → PDF con 6+ secciones incluyendo proyección y dimensiones
7. Clic "Escribirle por WhatsApp" → abre WhatsApp con mensaje pre-llenado
8. Verificar que un job de correo quedó en la cola: `php artisan queue:work --once`
