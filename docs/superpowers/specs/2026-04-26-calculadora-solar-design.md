# Calculadora Solar — Diseño de mejoras de extracción y cálculo

**Fecha:** 2026-04-26  
**Estado:** Aprobado

---

## Contexto

La calculadora solar actual extrae datos de boletas eléctricas chilenas con Claude Vision y dimensiona un sistema fotovoltaico on-grid. Dos problemas concretos:

1. El historial de consumo se extrae pero se ignora — el cálculo usa solo el mes actual.
2. Los datos de cliente (nombre, teléfono, email, región) no se propagan al formulario de confirmación — el usuario los ingresa a mano.

---

## Objetivo

- Usar el **promedio de los 6 consumos más altos** del historial como base del dimensionamiento.
- **Deducir la región** a partir de la dirección/comuna extraída.
- **Pre-rellenar el formulario de confirmación** con todos los datos personales del cliente.
- Calcular el **precio real del kWh** desde la boleta (`monto_total / consumo`).
- Eliminar el paso de contacto separado — unificarlo con la confirmación de datos.

---

## Arquitectura

```
PDF
 │
 ▼
BillExtractor::extract()
  · Envía PDF a Claude Vision (prompt extendido)
  · Devuelve array raw con campos nuevos
 │
 ▼
BillNormalizer::normalize(array $raw): array   ← NUEVO
  · Calcula precio_kwh_clp real
  · Deduce region via ComunaRegionMap
  · Calcula consumo_efectivo (avg top 6)
  · Devuelve array enriquecido
 │
 ▼
ExtractBillJob::handle()
  · Llama extractor → normalizer → guarda datos_boleta
 │
 ▼
CalculadoraWizard paso 3 — "Confirma tus datos"
  · Formulario con datos personales pre-rellenados
  · Al confirmar: calcula en silencio y va a resultados
```

---

## Componentes

### 1. `BillExtractor` — cambios al prompt

Campos nuevos que Claude debe extraer:

| Campo | Tipo | Descripción |
|---|---|---|
| `region` | `string\|null` | Claude intenta extraerla directo |
| `telefono` | `string\|null` | Si aparece en la boleta |
| `email` | `string\|null` | Si aparece en la boleta |
| `potencia_contratada_kw` | `number\|null` | Potencia contratada |

`max_tokens` sube de 1024 → 1536.

---

### 2. `BillNormalizer` — servicio nuevo

**Archivo:** `app/Services/BillNormalizer.php`

Método principal: `normalize(array $raw): array`

Aplica en orden:

**a) Precio kWh real**
```
precio_kwh_clp = monto_total_clp / consumo_kwh
```
Fallback: `Configuracion::get('precio_kwh_clp', 158)` si cualquier operando es null o 0.

**b) Región validada**
1. Si `$raw['region']` coincide exactamente con una de las 16 regiones → usar.
2. Si no → `ComunaRegionMap::lookup($raw['comuna'])`.
3. Si tampoco → `null` (usuario elige en el formulario).

**c) Consumo efectivo**
```
consumo_efectivo = avg(top 6 valores más altos de consumo_historico_kwh)
```
- Si hay menos de 6 meses → promedia los disponibles.
- Si `consumo_historico_kwh` es null o vacío → usa `consumo_kwh` del mes actual.

Devuelve el array `$raw` más los campos calculados:
```php
'precio_kwh_clp'   => float,
'consumo_efectivo' => float,
'region'           => string|null,
```

---

### 3. `ComunaRegionMap` — tabla estática nueva

**Archivo:** `app/Services/ComunaRegionMap.php`

Clase con un método estático `lookup(string $comuna): ?string` que consulta un array interno con las 346 comunas de Chile mapeadas a sus 16 regiones. Normaliza tildes y mayúsculas antes de comparar.

---

### 4. `ExtractBillJob` — cambio mínimo

```php
$data = $extractor->extract($this->pdfPath);
$data = app(BillNormalizer::class)->normalize($data);  // línea nueva
$this->solicitud->update(['datos_boleta' => $data, 'estado' => 'completado']);
```

---

### 5. `CalculadoraWizard` — wizard de 4 pasos (antes 5)

#### Pasos revisados

| # interno | Etiqueta visible | Descripción |
|---|---|---|
| 1 | Boleta | Subir PDF |
| 2 | — (oculto) | Polling del job |
| 3 | Datos | Confirmar datos del cliente |
| 4 | Resultado | Resultado del dimensionamiento |

El paso 4 (contacto separado) **desaparece** — se fusiona con el paso 3.

#### Paso 3 — Formulario de confirmación de datos

Todos los campos son editables. Los extraídos de la boleta vienen pre-rellenados:

| Campo | Fuente | Requerido |
|---|---|---|
| Nombre | `nombre_cliente` | Sí |
| RUT | `rut` | No |
| Dirección | `direccion` | No |
| Comuna | `comuna` | No |
| Región | deducida automáticamente | Sí (selector) |
| Teléfono | `telefono` | Sí |
| Email | `email` | Sí |
| Empresa | vacío | No |

Al confirmar (`confirmarDatos()`):
- Guarda los datos de contacto en la solicitud.
- Llama a `OngridCalculator` con valores internos (`consumo_efectivo`, `precio_kwh_clp`, `tipo_medidor`, `region`).
- Avanza al paso de resultados.

El usuario **nunca ve** los campos técnicos (consumo, tipo medidor, tarifa, precio kWh).

#### Cambios en `confirmarDatos()`

```php
// Usa consumo_efectivo en lugar de consumo_kwh
'consumo_kwh'    => (float) ($this->datosBoleta['consumo_efectivo'] ?? $this->datosBoleta['consumo_kwh']),
// Usa precio real de la boleta
'precio_kwh_clp' => (float) ($this->datosBoleta['precio_kwh_clp'] ?? Configuracion::get('precio_kwh_clp', 158)),
```

Validaciones del paso 3:
```php
'nombre'   => 'required|string|max:100',
'telefono' => 'required|string|max:20',
'email'    => 'required|email:rfc|max:150',
'datosBoleta.region' => 'required|string',
```

---

## Datos guardados en `calculadora_solicitudes.datos_boleta`

El campo JSON queda con estructura completa:

```json
{
  "nombre_cliente": "Juan Pérez",
  "rut": "12.345.678-9",
  "numero_cliente": "123456",
  "direccion": "Av. Providencia 1234",
  "comuna": "Providencia",
  "region": "Metropolitana de Santiago",
  "telefono": "+56912345678",
  "email": "juan@email.com",
  "potencia_contratada_kw": 5.75,
  "consumo_kwh": 320,
  "consumo_historico_kwh": [310, 290, 340, 380, 360, 300, 320, 290, 310, 340, 360, 380],
  "monto_total_clp": 54400,
  "tipo_tarifa": "BT1",
  "distribuidora": "Enel",
  "tipo_medidor": "monofasico",
  "precio_kwh_clp": 170.0,
  "consumo_efectivo": 365.0
}
```

---

## Reglas de negocio clave

- **Consumo efectivo:** promedio de los N más altos donde N = min(6, len(historial)). Si historial vacío → `consumo_kwh`.
- **Precio kWh:** `monto_total_clp / consumo_kwh`. Fallback al config si resultado ≤ 0 o cualquier operando es null.
- **Región:** Claude → tabla comunas → null. Nunca falla el flujo; el usuario puede completarla si queda vacía.
- **Contacto requerido:** nombre, teléfono y email son requeridos en el paso 3. Si Claude no los extrajo, el usuario los ingresa.

---

## Archivos afectados

| Archivo | Acción |
|---|---|
| `app/Services/BillExtractor.php` | Modificar prompt + subir max_tokens |
| `app/Services/BillNormalizer.php` | Crear |
| `app/Services/ComunaRegionMap.php` | Crear |
| `app/Jobs/ExtractBillJob.php` | Agregar llamada a BillNormalizer |
| `app/Livewire/CalculadoraWizard.php` | Refactorizar paso 3, eliminar paso 4, actualizar confirmarDatos() |
| `resources/views/livewire/calculadora-wizard.blade.php` | Refactorizar vista paso 3, eliminar vista paso 4 |
