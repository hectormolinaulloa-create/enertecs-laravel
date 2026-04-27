# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Backend**: Laravel 11, PHP 8.2+, MySQL
- **Frontend**: Blade + Livewire v3 + Tailwind CSS v4 (via `@tailwindcss/vite`)
- **Build**: Vite 6
- **Admin**: Filament v3
- **Queue**: Database driver (single `jobs` table, no Redis)
- **PDFs**: DomPDF + Blade template (`resources/views/pdf/`)
- **External APIs**: Anthropic Claude Vision (bill extraction), Victron VRM (solar monitoring)

## Comandos

```bash
# Desarrollo completo (servidor + queue + logs + Vite)
composer dev

# Por separado
php artisan serve
npm run dev
php artisan queue:listen --tries=1

# Producción
npm run build
composer install --no-dev

# Tests
php artisan test
php artisan test tests/Feature/ExampleTest.php   # test individual

# Linting PHP
php artisan pint

# Base de datos
php artisan migrate --seed
php artisan migrate:fresh --seed
```

## Arquitectura

### Rutas (`routes/web.php`)

Todas las rutas están en `web.php` — no hay `api.php`. Las rutas "API" del frontend también van aquí con prefijo `/api/`:

- `GET /` → `PaginaController@home`
- `GET /servicios/{slug}` → `PaginaController@servicio` (Servicio model por slug)
- `GET /calculadora/solar-ongrid` → Livewire `CalculadoraWizard`
- `GET /api/vrm/chart` → datos del dashboard VRM (throttle 30/min)
- `GET /calculadora/job-status/{solicitud}` → polling UUID del job de extracción
- `GET /calculadora/solar-ongrid/informe/{solicitud}` → descarga PDF
- `GET /run-migrations?token=...` → migraciones protegidas por token (solo producción)

### Flujo del Calculador Solar (feature principal)

1. **Livewire CalculadoraWizard** — 5 pasos: subir PDF → polling → confirmar datos → contacto → resultados
2. La subida despacha `ExtractBillJob` a la cola `database`
3. `ExtractBillJob` usa `BillExtractor` → llama Claude Vision (Anthropic API) → extrae JSON del PDF
4. El frontend hace polling a `/calculadora/job-status/{uuid}` hasta que `estado = completado`
5. `OngridCalculator` dimensiona el sistema (kWp, paneles, strings, ROI, CO2) con HSP por región chilena
6. `InformeGenerator` genera el PDF con DomPDF

### Patrones clave

**UUID en rutas**: `CalculadoraSolicitud` expone UUID en lugar de ID (`getRouteKeyName() → 'uuid'`) para evitar IDOR.

**Scopes activos**: `Servicio::activo()` y `Proyecto::activo()` filtran por `activo = true` + orden por campo `orden`.

**Modelo Configuracion**: tabla key-value para parámetros dinámicos (precios kWh, costo por kWp). Acceder con `Configuracion::get('clave', $default)`.

**Dashboard VRM**: `VrmDashboard` (Livewire) consulta Victron Energy API. Los datos de gráfico se cachean (5–60 min según rango). Helpers SVG en `app/Helpers/VrmHelpers.php` generan gauge de SOC y mini spark chart.

**Seguridad**: middleware `SecurityHeaders` aplica CSP, X-Frame-Options, y otras cabeceras en todas las respuestas.

### Filament Admin

Panel en `/admin`. Recursos en `app/Filament/Resources/`: `ServicioResource`, `ProyectoResource`, `CalculadoraSolicitudResource`, `CertificacionResource`, `ConfiguracionResource`.

### Frontend

- El layout maestro es `resources/views/layouts/app.blade.php` (navbar + footer + stacks de Livewire)
- Tailwind v4: sin `tailwind.config.js`, la configuración del tema va en `resources/css/app.css` con variables CSS
- No hay Vue ni React; toda la interactividad usa Livewire v3

## Variables de entorno requeridas

```
ANTHROPIC_API_KEY=     # Claude Vision para extracción de boletas
VRM_TOKEN=             # Token API Victron
VRM_SITE_ID=           # ID sitio Victron
APP_MIGRATION_TOKEN=   # Token para /run-migrations en producción
APP_TIMEZONE=America/Santiago
APP_LOCALE=es
```

## Despliegue

Ver `DEPLOY.md` para instrucciones completas de cPanel. El proyecto está pensado para hosting compartido (sin Docker, sin Redis, queue database).
