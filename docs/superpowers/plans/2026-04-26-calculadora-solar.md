# Calculadora Solar — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enriquecer la extracción de boletas eléctricas con todos los datos del cliente, calcular el consumo efectivo como promedio de los 6 meses más altos del historial, y unificar confirmación de datos + contacto en un solo paso.

**Architecture:** Se introduce `BillNormalizer` (post-procesa el array crudo de Claude) y `ComunaRegionMap` (tabla estática de 346 comunas → 16 regiones). `ExtractBillJob` encadena extractor → normalizer. `CalculadoraWizard` pasa de 5 a 4 pasos internos eliminando el paso de contacto separado.

**Tech Stack:** PHP 8.2, Laravel 11, Livewire v3, PHPUnit (php artisan test)

---

## Mapa de archivos

| Archivo | Acción |
|---|---|
| `app/Services/ComunaRegionMap.php` | Crear — tabla estática comuna→región |
| `app/Services/BillNormalizer.php` | Crear — normaliza array crudo de Claude |
| `app/Services/BillExtractor.php` | Modificar — prompt extendido, max_tokens 1536 |
| `app/Jobs/ExtractBillJob.php` | Modificar — encadenar BillNormalizer |
| `app/Livewire/CalculadoraWizard.php` | Modificar — paso 3 unificado, confirmarDatos() |
| `resources/views/livewire/calculadora-wizard.blade.php` | Modificar — nueva vista paso 3, eliminar paso 4 |
| `tests/Unit/Services/ComunaRegionMapTest.php` | Crear |
| `tests/Unit/Services/BillNormalizerTest.php` | Crear |

---

## Task 1: ComunaRegionMap

**Files:**
- Create: `app/Services/ComunaRegionMap.php`
- Create: `tests/Unit/Services/ComunaRegionMapTest.php`

- [ ] **Step 1: Crear el directorio de tests si no existe**

```bash
mkdir -p tests/Unit/Services
```

- [ ] **Step 2: Escribir el test**

Crear `tests/Unit/Services/ComunaRegionMapTest.php`:

```php
<?php
namespace Tests\Unit\Services;

use App\Services\ComunaRegionMap;
use Tests\TestCase;

class ComunaRegionMapTest extends TestCase
{
    public function test_lookup_por_comuna_exacta(): void
    {
        $this->assertSame('Metropolitana de Santiago', ComunaRegionMap::lookup('Santiago'));
        $this->assertSame('Valparaíso', ComunaRegionMap::lookup('Viña del Mar'));
        $this->assertSame('Biobío', ComunaRegionMap::lookup('Concepción'));
    }

    public function test_lookup_normaliza_tildes(): void
    {
        $this->assertSame('La Araucanía', ComunaRegionMap::lookup('Temuco'));
        $this->assertSame("O'Higgins", ComunaRegionMap::lookup('Rancagua'));
    }

    public function test_lookup_normaliza_mayusculas(): void
    {
        $this->assertSame('Metropolitana de Santiago', ComunaRegionMap::lookup('SANTIAGO'));
        $this->assertSame('Metropolitana de Santiago', ComunaRegionMap::lookup('providencia'));
    }

    public function test_lookup_retorna_null_para_comuna_desconocida(): void
    {
        $this->assertNull(ComunaRegionMap::lookup('CiudadInventada'));
    }

    public function test_lookup_string_vacio_retorna_null(): void
    {
        $this->assertNull(ComunaRegionMap::lookup(''));
    }
}
```

- [ ] **Step 3: Correr el test para verificar que falla**

```bash
php artisan test tests/Unit/Services/ComunaRegionMapTest.php
```

Esperado: ERROR — "Class App\Services\ComunaRegionMap not found"

- [ ] **Step 4: Crear ComunaRegionMap**

Crear `app/Services/ComunaRegionMap.php`:

```php
<?php
namespace App\Services;

class ComunaRegionMap
{
    private const MAP = [
        'Arica y Parinacota' => [
            'Arica', 'Camarones', 'Putre', 'General Lagos',
        ],
        'Tarapacá' => [
            'Iquique', 'Alto Hospicio', 'Pozo Almonte', 'Camiña', 'Colchane', 'Huara', 'Pica',
        ],
        'Antofagasta' => [
            'Antofagasta', 'Mejillones', 'Sierra Gorda', 'Taltal', 'Calama', 'Ollagüe', 'San Pedro de Atacama',
            'Tocopilla', 'María Elena',
        ],
        'Atacama' => [
            'Copiapó', 'Caldera', 'Tierra Amarilla', 'Chañaral', 'Diego de Almagro',
            'Vallenar', 'Alto del Carmen', 'Freirina', 'Huasco',
        ],
        'Coquimbo' => [
            'La Serena', 'Coquimbo', 'Andacollo', 'La Higuera', 'Paihuano', 'Vicuña',
            'Illapel', 'Canela', 'Los Vilos', 'Salamanca',
            'Ovalle', 'Combarbalá', 'Monte Patria', 'Punitaqui', 'Río Hurtado',
        ],
        'Valparaíso' => [
            'Valparaíso', 'Casablanca', 'Concón', 'Juan Fernández', 'Puchuncaví',
            'Quintero', 'Viña del Mar',
            'Isla de Pascua',
            'Los Andes', 'Calle Larga', 'Rinconada', 'San Esteban',
            'La Ligua', 'Cabildo', 'Papudo', 'Petorca', 'Zapallar',
            'Quillota', 'Calera', 'Hijuelas', 'La Cruz', 'Nogales',
            'San Antonio', 'Algarrobo', 'Cartagena', 'El Quisco', 'El Tabo', 'Santo Domingo',
            'San Felipe', 'Catemu', 'Llaillay', 'Panquehue', 'Putaendo', 'Santa María',
            'Quilpué', 'Limache', 'Olmué', 'Villa Alemana',
        ],
        'Metropolitana de Santiago' => [
            'Santiago', 'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central',
            'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 'La Pintana',
            'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul',
            'Maipú', 'Ñuñoa', 'Pedro Aguirre Cerda', 'Peñalolén', 'Providencia', 'Pudahuel',
            'Quilicura', 'Quinta Normal', 'Recoleta', 'Renca', 'San Joaquín', 'San Miguel',
            'San Ramón', 'Vitacura',
            'Puente Alto', 'Pirque', 'San José de Maipo',
            'Colina', 'Lampa', 'Tiltil',
            'San Bernardo', 'Buin', 'Calera de Tango', 'Paine',
            'Melipilla', 'Alhué', 'Curacaví', 'María Pinto', 'San Pedro',
            'Talagante', 'El Monte', 'Isla de Maipo', 'Padre Hurtado', 'Peñaflor',
        ],
        "O'Higgins" => [
            'Rancagua', 'Codegua', 'Coinco', 'Coltauco', 'Doñihue', 'Graneros', 'Las Cabras',
            'Machalí', 'Malloa', 'Mostazal', 'Olivar', 'Peumo', 'Pichidegua', 'Quinta de Tilcoco',
            'Rengo', 'Requínoa', 'San Vicente',
            'Pichilemu', 'La Estrella', 'Litueche', 'Marchihue', 'Navidad', 'Paredones',
            'San Fernando', 'Chépica', 'Chimbarongo', 'Lolol', 'Nancagua', 'Palmilla',
            'Peralillo', 'Placilla', 'Pumanque', 'Santa Cruz',
        ],
        'Maule' => [
            'Talca', 'Constitución', 'Curepto', 'Empedrado', 'Maule', 'Pelarco', 'Pencahue',
            'Río Claro', 'San Clemente', 'San Rafael',
            'Cauquenes', 'Chanco', 'Pelluhue',
            'Curicó', 'Hualañé', 'Licantén', 'Molina', 'Rauco', 'Romeral', 'Sagrada Familia', 'Teno', 'Vichuquén',
            'Linares', 'Colbún', 'Longaví', 'Parral', 'Retiro', 'San Javier', 'Villa Alegre', 'Yerbas Buenas',
        ],
        'Ñuble' => [
            'Chillán', 'Bulnes', 'Chillán Viejo', 'El Carmen', 'Pemuco', 'Pinto', 'Quillón',
            'San Ignacio', 'Yungay',
            'Cobquecura', 'Coelemu', 'Ninhue', 'Portezuelo', 'Quirihue', 'Ránquil', 'Treguaco',
            'Coihueco', 'Ñiquén', 'San Carlos', 'San Fabián', 'San Nicolás',
        ],
        'Biobío' => [
            'Concepción', 'Coronel', 'Chiguayante', 'Florida', 'Hualpén', 'Hualqui', 'Lota',
            'Penco', 'San Pedro de la Paz', 'Santa Juana', 'Talcahuano', 'Tomé', 'Chillán',
            'Lebu', 'Arauco', 'Cañete', 'Contulmo', 'Curanilahue', 'Los Álamos', 'Tirúa',
            'Los Ángeles', 'Antuco', 'Cabrero', 'Laja', 'Mulchén', 'Nacimiento', 'Negrete',
            'Quilaco', 'Quilleco', 'San Rosendo', 'Santa Bárbara', 'Tucapel', 'Yumbel',
            'Alto Biobío',
        ],
        'La Araucanía' => [
            'Temuco', 'Carahue', 'Cunco', 'Curarrehue', 'Freire', 'Galvarino', 'Gorbea',
            'Lautaro', 'Loncoche', 'Melipeuco', 'Nueva Imperial', 'Padre Las Casas',
            'Perquenco', 'Pitrufquén', 'Pucón', 'Saavedra', 'Teodoro Schmidt',
            'Toltén', 'Vilcún', 'Villarrica', 'Cholchol',
            'Angol', 'Collipulli', 'Curacautín', 'Ercilla', 'Lonquimay', 'Los Sauces',
            'Lumaco', 'Purén', 'Renaico', 'Traiguén', 'Victoria',
        ],
        'Los Ríos' => [
            'Valdivia', 'Corral', 'Futrono', 'La Unión', 'Lago Ranco', 'Lanco',
            'Los Lagos', 'Máfil', 'Mariquina', 'Paillaco', 'Panguipulli', 'Río Bueno',
        ],
        'Los Lagos' => [
            'Puerto Montt', 'Calbuco', 'Cochamó', 'Fresia', 'Frutillar', 'Los Muermos',
            'Llanquihue', 'Maullín', 'Puerto Varas',
            'Castro', 'Ancud', 'Chonchi', 'Curaco de Vélez', 'Dalcahue', 'Puqueldón',
            'Queilén', 'Quellón', 'Quemchi', 'Quinchao',
            'Osorno', 'Puerto Octay', 'Purranque', 'Puyehue', 'Río Negro', 'San Juan de la Costa', 'San Pablo',
            'Chaitén', 'Futaleufú', 'Hualaihué', 'Palena',
        ],
        'Aysén' => [
            'Coihaique', 'Lago Verde',
            'Aysén', 'Cisnes', 'Guaitecas',
            'Cochrane', "O'Higgins", 'Tortel',
            'Chile Chico', 'Río Ibáñez',
        ],
        'Magallanes' => [
            'Punta Arenas', 'Laguna Blanca', 'Río Verde', 'San Gregorio',
            'Cabo de Hornos', 'Antártica',
            'Porvenir', 'Primavera', 'Timaukel',
            'Natales', 'Torres del Paine',
        ],
    ];

    public static function lookup(string $comuna): ?string
    {
        if ($comuna === '') {
            return null;
        }
        $needle = mb_strtolower(self::strip($comuna));
        foreach (self::MAP as $region => $comunas) {
            foreach ($comunas as $c) {
                if (mb_strtolower(self::strip($c)) === $needle) {
                    return $region;
                }
            }
        }
        return null;
    }

    private static function strip(string $s): string
    {
        return strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
        ]);
    }
}
```

- [ ] **Step 5: Correr tests y verificar que pasan**

```bash
php artisan test tests/Unit/Services/ComunaRegionMapTest.php
```

Esperado: 5 tests, PASS

- [ ] **Step 6: Commit**

```bash
git add app/Services/ComunaRegionMap.php tests/Unit/Services/ComunaRegionMapTest.php
git commit -m "feat(calculadora): ComunaRegionMap — tabla estática 346 comunas → 16 regiones"
```

---

## Task 2: BillNormalizer

**Files:**
- Create: `app/Services/BillNormalizer.php`
- Create: `tests/Unit/Services/BillNormalizerTest.php`

- [ ] **Step 1: Escribir el test**

Crear `tests/Unit/Services/BillNormalizerTest.php`:

```php
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
            'nombre_cliente'       => 'Test Cliente',
            'rut'                  => '12.345.678-9',
            'numero_cliente'       => '123',
            'direccion'            => 'Av. Test 123',
            'comuna'               => 'Santiago',
            'region'               => null,
            'telefono'             => null,
            'email'                => null,
            'potencia_contratada_kw' => null,
            'consumo_kwh'          => 300,
            'consumo_historico_kwh'=> null,
            'monto_total_clp'      => 47400,
            'tipo_tarifa'          => 'BT1',
            'distribuidora'        => 'Enel',
            'tipo_medidor'         => 'monofasico',
        ], $overrides);
    }
}
```

- [ ] **Step 2: Correr test para verificar que falla**

```bash
php artisan test tests/Unit/Services/BillNormalizerTest.php
```

Esperado: ERROR — "Class App\Services\BillNormalizer not found"

- [ ] **Step 3: Crear BillNormalizer**

Crear `app/Services/BillNormalizer.php`:

```php
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
```

- [ ] **Step 4: Correr tests y verificar que pasan**

```bash
php artisan test tests/Unit/Services/BillNormalizerTest.php
```

Esperado: 10 tests, PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/BillNormalizer.php tests/Unit/Services/BillNormalizerTest.php
git commit -m "feat(calculadora): BillNormalizer — consumo efectivo, precio real kWh, deducción región"
```

---

## Task 3: BillExtractor — prompt extendido

**Files:**
- Modify: `app/Services/BillExtractor.php`

- [ ] **Step 1: Reemplazar el PROMPT y subir max_tokens**

En `app/Services/BillExtractor.php`, reemplazar la constante `PROMPT` y el valor de `max_tokens`:

```php
private const PROMPT = <<<'PROMPT'
Eres un extractor de datos de boletas eléctricas chilenas.
Extrae los siguientes campos del documento y devuelve SOLO un JSON válido, sin explicación.
Si un campo no se encuentra, usa null.

Campos requeridos:
{
  "nombre_cliente": string | null,
  "rut": string | null,
  "numero_cliente": string | null,
  "direccion": string | null,
  "comuna": string | null,
  "region": string | null,
  "telefono": string | null,
  "email": string | null,
  "potencia_contratada_kw": number | null,
  "consumo_kwh": number | null,
  "consumo_historico_kwh": number[] | null,
  "monto_total_clp": number | null,
  "tipo_tarifa": string | null,
  "distribuidora": string | null,
  "tipo_medidor": "monofasico" | "trifasico" | null
}

Notas:
- tipo_medidor: busca "monofásico", "trifásico", "1F", "3F", o el número de fases del medidor
- consumo_kwh: el consumo del mes actual, solo el número sin unidades
- consumo_historico_kwh: array con los valores de consumo mensual en kWh de los meses anteriores, de más antiguo a más reciente. Si no hay historial, usa null.
- monto_total_clp: solo el número total a pagar en pesos, sin puntos ni símbolos
- tipo_tarifa: BT1, BT2, BT3, AT, etc.
- region: nombre de la región de Chile tal como aparece en la boleta o se puede deducir de la dirección. Una de: Arica y Parinacota, Tarapacá, Antofagasta, Atacama, Coquimbo, Valparaíso, Metropolitana de Santiago, O'Higgins, Maule, Ñuble, Biobío, La Araucanía, Los Ríos, Los Lagos, Aysén, Magallanes.
- telefono: número de teléfono del cliente si aparece en la boleta
- email: correo electrónico del cliente si aparece en la boleta
- potencia_contratada_kw: potencia contratada en kW si aparece en la boleta
PROMPT;
```

Y en el array de la petición HTTP, cambiar `'max_tokens' => 1024` a `'max_tokens' => 1536`.

- [ ] **Step 2: Verificar que el archivo compila sin errores**

```bash
php artisan tinker --no-ansi --execute="new App\Services\BillExtractor(); echo 'OK';" 2>&1
```

Esperado: `OK`

- [ ] **Step 3: Commit**

```bash
git add app/Services/BillExtractor.php
git commit -m "feat(calculadora): BillExtractor — campos region/telefono/email/potencia, max_tokens 1536"
```

---

## Task 4: ExtractBillJob — encadenar BillNormalizer

**Files:**
- Modify: `app/Jobs/ExtractBillJob.php`

- [ ] **Step 1: Agregar import y llamada al normalizer**

En `app/Jobs/ExtractBillJob.php`, agregar el import de `BillNormalizer` junto a los existentes:

```php
use App\Services\BillNormalizer;
```

Reemplazar el bloque `try` del método `handle()`:

```php
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
```

- [ ] **Step 2: Verificar que el archivo compila**

```bash
php artisan tinker --no-ansi --execute="echo 'OK';" 2>&1
```

Esperado: `OK`

- [ ] **Step 3: Commit**

```bash
git add app/Jobs/ExtractBillJob.php
git commit -m "feat(calculadora): ExtractBillJob encadena BillNormalizer tras extracción"
```

---

## Task 5: CalculadoraWizard — lógica PHP

**Files:**
- Modify: `app/Livewire/CalculadoraWizard.php`

- [ ] **Step 1: Reemplazar el archivo completo**

Reemplazar `app/Livewire/CalculadoraWizard.php` con:

```php
<?php
namespace App\Livewire;

use App\Jobs\ExtractBillJob;
use App\Models\CalculadoraSolicitud;
use App\Models\Configuracion;
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
```

- [ ] **Step 2: Verificar que el componente Livewire carga sin errores**

```bash
php artisan tinker --no-ansi --execute="echo app(\App\Livewire\CalculadoraWizard::class) ? 'OK' : 'FAIL';" 2>&1
```

Esperado: `OK`

- [ ] **Step 3: Commit**

```bash
git add app/Livewire/CalculadoraWizard.php
git commit -m "feat(calculadora): wizard unifica paso contacto+datos, usa consumo_efectivo y precio real"
```

---

## Task 6: Vista Blade — nuevo paso 3 y eliminación paso 4

**Files:**
- Modify: `resources/views/livewire/calculadora-wizard.blade.php`

- [ ] **Step 1: Reemplazar la vista completa**

Reemplazar `resources/views/livewire/calculadora-wizard.blade.php` con:

```blade
<div class="min-h-screen bg-[#0a1628]">

    {{-- Barra de progreso --}}
    @php
        $stepLabels = ['Boleta', 'Datos', 'Resultado'];
        $stepIdx = match($step) { 1, 2 => 0, 3 => 1, 4 => 2, default => 0 };
    @endphp
    <div class="border-b border-white/5 bg-[#0d1e3a]">
        <div class="max-w-2xl mx-auto px-4 py-4 flex justify-between">
            @foreach($stepLabels as $i => $label)
            <div class="flex items-center gap-2 text-xs font-bold {{ $i <= $stepIdx ? 'text-[#0067FF]' : 'text-white/20' }}">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs
                    {{ $i < $stepIdx ? 'bg-[#0067FF] text-white' : ($i === $stepIdx ? 'border-2 border-[#0067FF] text-[#0067FF]' : 'border border-white/20 text-white/20') }}">
                    {{ $i < $stepIdx ? '✓' : $i + 1 }}
                </span>
                <span class="hidden sm:inline">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Error global --}}
    @if($error)
    <div class="max-w-2xl mx-auto px-4 pt-4">
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-red-400 text-sm">
            {{ $error }}
            <button wire:click="$set('error', '')" class="ml-2 underline">Cerrar</button>
        </div>
    </div>
    @endif

    {{-- Step 1: Upload --}}
    @if($step === 1)
    <div class="py-12 px-4">

        {{-- Guía introductoria --}}
        <div class="max-w-2xl mx-auto mb-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-black text-white mb-1">Descubra si la energía solar es para usted</h1>
                <p class="text-white/45 text-sm">Gratuito · Sin visitas · Resultado inmediato</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl px-4 py-3 mb-6"
                 style="background:rgba(0,103,255,0.10);border:1px solid rgba(0,103,255,0.25)">
                <span class="text-2xl">📄</span>
                <p class="text-white/80 text-sm">
                    Necesita su <span class="text-white font-bold">boleta eléctrica en PDF</span> — descárguela desde el sitio web o app de su distribuidora (CGE, Enel, Frontel, Chilquinta, Edelmag…)
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    ['icon' => '📤', 'label' => "Suba\nsu boleta"],
                    ['icon' => '✏️', 'label' => "Confirme\nsus datos"],
                    ['icon' => '📊', 'label' => "Reciba\nsu informe"],
                ] as $i => $s)
                <div class="relative flex flex-col items-center text-center gap-2 bg-[#0d1e3a] border border-white/10 rounded-xl py-4 px-2">
                    @if($i < 2)
                    <span class="absolute top-1/2 -translate-y-1/2 text-white/20 text-xs z-10" style="right:-6px">›</span>
                    @endif
                    <span class="text-3xl">{{ $s['icon'] }}</span>
                    <span class="text-[#0067FF] text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"
                          style="background:rgba(0,103,255,0.15)">{{ $i + 1 }}</span>
                    <p class="text-white/70 text-xs leading-tight" style="white-space:pre-line">{{ $s['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="max-w-2xl mx-auto border-t border-white/10 mb-8"></div>

        {{-- Upload widget --}}
        <div class="w-full max-w-lg mx-auto">
            <h2 class="text-2xl font-black text-white mb-2 text-center">Sube tu boleta eléctrica</h2>
            <p class="text-white/50 text-sm text-center mb-8">La IA extraerá automáticamente todos los datos necesarios</p>

            <form wire:submit="subirPdf" x-data="{ dragging: false }">
                <div
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave="dragging = false"
                    x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                    x-on:click="$refs.fileInput.click()"
                    class="border-2 border-dashed rounded-2xl p-12 text-center cursor-pointer transition-all"
                    :class="dragging ? 'border-[#0067FF] bg-[#0067FF]/10' : 'border-white/20 hover:border-white/40'">

                    <input x-ref="fileInput" type="file" wire:model="pdf" accept=".pdf" class="hidden" id="pdf-input">

                    <div wire:loading wire:target="subirPdf" class="space-y-3">
                        <div class="w-8 h-8 border-2 border-[#0067FF] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-white/60 text-sm">Analizando boleta con IA…</p>
                    </div>
                    <div wire:loading.remove wire:target="subirPdf" class="space-y-3">
                        <div class="text-4xl">📄</div>
                        <p class="text-white font-bold">
                            @if($pdf) {{ $pdf->getClientOriginalName() }}
                            @else Arrastra tu boleta PDF aquí @endif
                        </p>
                        <p class="text-white/40 text-sm">{{ $pdf ? 'Listo para analizar' : 'o haz clic para seleccionar' }}</p>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="subirPdf"
                    class="w-full mt-4 bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="subirPdf">Analizar boleta con IA</span>
                    <span wire:loading wire:target="subirPdf">Analizando…</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Step 2: Procesando --}}
    @if($step === 2)
    <div class="text-center py-20" wire:poll.2000ms="checkJobStatus">
        <div class="w-12 h-12 border-2 border-[#0067FF] border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-white font-bold">Analizando tu boleta…</p>
        <p class="text-white/40 text-sm mt-2">La inteligencia artificial está extrayendo tus datos.</p>
    </div>
    @endif

    {{-- Step 3: Confirma datos del cliente --}}
    @if($step === 3)
    <div class="max-w-2xl mx-auto py-12 px-6">
        <h2 class="text-white font-black text-xl mb-2">Confirma tus datos</h2>
        <p class="text-white/40 text-sm mb-6">Hemos extraído estos datos de tu boleta. Revisa y completa lo que falte.</p>

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-4">
            <ul class="text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="space-y-4">

            {{-- Nombre --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Nombre completo *</label>
                <input type="text" wire:model="datosBoleta.nombre_cliente"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
            </div>

            {{-- RUT --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">RUT</label>
                <input type="text" wire:model="datosBoleta.rut"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
            </div>

            {{-- Dirección y comuna en fila --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Dirección</label>
                    <input type="text" wire:model="datosBoleta.direccion"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
                </div>
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Comuna</label>
                    <input type="text" wire:model="datosBoleta.comuna"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
                </div>
            </div>

            {{-- Región --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Región *</label>
                <select wire:model="datosBoleta.region"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
                    <option value="">Selecciona tu región</option>
                    <option>Arica y Parinacota</option>
                    <option>Tarapacá</option>
                    <option>Antofagasta</option>
                    <option>Atacama</option>
                    <option>Coquimbo</option>
                    <option>Valparaíso</option>
                    <option>Metropolitana de Santiago</option>
                    <option>O'Higgins</option>
                    <option>Maule</option>
                    <option>Ñuble</option>
                    <option>Biobío</option>
                    <option>La Araucanía</option>
                    <option>Los Ríos</option>
                    <option>Los Lagos</option>
                    <option>Aysén</option>
                    <option>Magallanes</option>
                </select>
            </div>

            {{-- Teléfono y email en fila --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Teléfono *</label>
                    <input type="tel" wire:model="datosBoleta.telefono"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none"
                        placeholder="+56 9 XXXX XXXX">
                </div>
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Email *</label>
                    <input type="email" wire:model="datosBoleta.email"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none"
                        placeholder="correo@ejemplo.cl">
                </div>
            </div>

            {{-- Empresa --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Empresa (opcional)</label>
                <input type="text" wire:model="datosBoleta.empresa"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
            </div>

            <button wire:click="confirmarDatos" wire:loading.attr="disabled"
                class="w-full bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="confirmarDatos">Ver mi resultado solar</span>
                <span wire:loading wire:target="confirmarDatos">Calculando…</span>
            </button>

            <button wire:click="$set('step', 1)" class="w-full text-white/40 text-sm hover:text-white/60 transition-colors">
                ← Volver
            </button>
        </div>
    </div>
    @endif

    {{-- Step 4: Resultado --}}
    @if($step === 4)
    <div class="max-w-2xl mx-auto py-12 px-6">
        <h2 class="text-white font-black text-xl mb-6">Tu sistema solar estimado</h2>
        <div class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-6 space-y-4 mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-white/40 text-xs">Potencia</p>
                    <p class="text-white font-black text-2xl">{{ number_format($resultado['potencia_real_kwp'] ?? 0, 2) }} kWp</p>
                </div>
                <div>
                    <p class="text-white/40 text-xs">Paneles</p>
                    <p class="text-white font-black text-2xl">{{ $resultado['n_paneles'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-white/40 text-xs">Ahorro mensual est.</p>
                    <p class="text-green-400 font-black text-xl">${{ number_format($resultado['ahorro_mensual_clp'] ?? 0) }} CLP</p>
                </div>
                <div>
                    <p class="text-white/40 text-xs">Retorno inversión</p>
                    <p class="text-white font-black text-xl">{{ number_format($resultado['roi_anos'] ?? 0, 1) }} años</p>
                </div>
            </div>
        </div>
        @if($solicitudUuid)
        <a href="{{ route('calculadora.informe', $solicitudUuid) }}"
           class="w-full flex items-center justify-center gap-2 bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors">
            Descargar informe PDF
        </a>
        @endif
        <button wire:click="reiniciar" class="w-full mt-3 text-white/40 text-sm hover:text-white/60 transition-colors">
            Nueva consulta
        </button>
    </div>
    @endif

</div>
```

- [ ] **Step 2: Verificar que la página carga sin errores**

```bash
curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/calculadora/solar-ongrid
```

Esperado: `200`

- [ ] **Step 3: Correr todos los tests**

```bash
php artisan test
```

Esperado: todos los tests en PASS

- [ ] **Step 4: Commit final**

```bash
git add resources/views/livewire/calculadora-wizard.blade.php
git commit -m "feat(calculadora): wizard 3 pasos — formulario unificado datos+contacto pre-rellenado"
```
