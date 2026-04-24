<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; margin: 0; padding: 20px; }
  .header { background: #0067FF; color: white; padding: 20px; margin-bottom: 20px; }
  .header h1 { margin: 0; font-size: 18px; }
  .header p  { margin: 4px 0 0; font-size: 11px; opacity: 0.8; }
  .section { margin-bottom: 16px; }
  .section h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #0067FF;
                border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; }
  td { padding: 4px 8px; }
  td:first-child { color: #6b7280; width: 45%; }
  td:last-child { font-weight: bold; }
  .highlight { background: #eff6ff; border-left: 3px solid #0067FF; padding: 8px 12px; margin: 12px 0; }
  .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px;
            font-size: 9px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="header">
  <h1>Informe Solar OnGrid — Enertecs SpA</h1>
  <p>Fecha: {{ now()->format('d/m/Y') }} · Folio: #{{ $solicitud->id }}</p>
</div>

@php $d = $solicitud->datos_boleta ?? []; $r = $solicitud->resultado ?? []; @endphp

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
    <tr><td>Panel seleccionado</td><td>{{ ($r['panel']['marca'] ?? '') }} {{ ($r['panel']['modelo'] ?? '') }} {{ ($r['panel']['potencia_wp'] ?? '') }}Wp</td></tr>
    <tr><td>Inversor seleccionado</td><td>{{ ($r['inversor']['marca'] ?? '') }} {{ ($r['inversor']['modelo'] ?? '') }}</td></tr>
    <tr><td>Área requerida</td><td>{{ number_format($r['area_m2'] ?? 0, 1) }} m²</td></tr>
    <tr><td>HSP de la región</td><td>{{ $r['hsp'] ?? '—' }} h/día</td></tr>
  </table>
</div>

<div class="section">
  <h2>Proyección económica</h2>
  <div class="highlight">
    <table>
      <tr><td>Producción mensual estimada</td><td>{{ number_format($r['produccion_mensual_kwh'] ?? 0) }} kWh/mes</td></tr>
      <tr><td>Ahorro mensual estimado</td><td>${{ number_format($r['ahorro_mensual_clp'] ?? 0) }} CLP</td></tr>
      <tr><td>Retorno de inversión</td><td>{{ number_format($r['roi_anos'] ?? 0, 1) }} años</td></tr>
      <tr><td>CO₂ evitado anualmente</td><td>{{ number_format($r['co2_kg_anual'] ?? 0) }} kg</td></tr>
    </table>
  </div>
</div>

<div class="footer">
  Enertecs SpA · Ingeniería Eléctrica · Punta Arenas, Chile<br>
  Este informe es una estimación referencial. Los valores reales pueden variar según las condiciones del sitio.
</div>
</body>
</html>
