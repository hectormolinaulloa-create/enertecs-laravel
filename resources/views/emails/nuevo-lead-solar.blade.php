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
    <p>Análisis completado el {{ $solicitud->updated_at->format('d/m/Y H:i') }}</p>
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
    @if($solicitud->consentimiento)
    ✅ El cliente autorizó el uso de sus datos de contacto para recibir información sobre su proyecto solar.
    @else
    ⚠️ El cliente <strong>no autorizó</strong> el uso de sus datos de contacto.
    @endif
  </div>

  <div class="footer">
    Enertecs SpA · este correo fue generado automáticamente por el sistema de análisis solar con IA.
  </div>
</div>
</body>
</html>
