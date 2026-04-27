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
