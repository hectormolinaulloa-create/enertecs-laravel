{{-- VRM Dashboard — réplica de VrmSection.tsx --}}
<div wire:poll.300000ms="fetchData"
     style="background: linear-gradient(180deg,#04091A 0%,#050C1C 60%,#040818 100%)">

@if($error)
{{-- Estado de error --}}
<div class="px-6 py-8 text-center">
    <p style="color:#3D5A82" class="text-sm">{{ $errorMsg }}</p>
</div>

@else

@php
$totals       = $snapshot['totals']       ?? [];
$instalaciones= $snapshot['instalaciones']?? [];
$trend30d     = $snapshot['trend30d']     ?? [];
$trendVals    = array_column($trend30d, 'kwh');
$solarKwh     = $totals['solar_kwh']       ?? 0;
$co2Ton       = round(($totals['co2_kg'] ?? 0) / 1000, 2);
$capacidadKwp = round(($totals['capacidad_wp'] ?? 0) / 1000, 1);
$plantasAct   = $totals['plantas_activas']  ?? 0;
$plantasTot   = $totals['plantas_total']    ?? 0;
$pvWattsLive  = $totals['pv_watts_live']    ?? 0;

$mapMarkers = collect($instalaciones)->map(fn($p) => [
    'lat'    => $p['lat'],
    'lng'    => $p['lng'],
    'nombre' => $p['nombre'],
    'estado' => $p['estado'],
])->unique(fn($m) => "{$m['lat']},{$m['lng']}")->values()->toArray();
@endphp

{{-- ── Header ───────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3"
     style="border-bottom:1px solid #0F1D33">
    <div class="flex items-center gap-3">
        <span class="text-xs font-semibold uppercase tracking-widest" style="color:#8BADD4">
            Nuestros proyectos fotovoltaicos
        </span>
        <span class="hidden sm:inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider px-2 py-1 rounded"
              style="background:#3B82F614;border:1px solid #3B82F630;color:#60A5FA">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Victron
        </span>
    </div>
    <span class="text-xs font-semibold px-2 py-1 rounded"
          style="color:#22D3EE;background:#22D3EE15;border:1px solid #22D3EE30;letter-spacing:1px">
        ● EN VIVO
    </span>
</div>

{{-- ── KPI strip ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 px-4 sm:px-6 lg:px-8 py-4"
     style="border-bottom:1px solid #0F1D33">

    {{-- KPI: Producción solar --}}
    <div x-data="countUp({{ $solarKwh }}, 2)"
         x-intersect.once="start()"
         class="rounded-xl px-4 py-4 flex flex-col gap-2 relative overflow-hidden"
         style="background:rgba(9,21,41,0.88);border:1px solid #1A2D4A">
        <div class="text-xs font-medium uppercase tracking-widest" style="color:#8BADD4">Producción solar</div>
        <div class="flex items-end gap-1">
            <span class="text-3xl font-black leading-none" style="color:#60A5FA" x-text="display"></span>
            <span class="text-sm mb-0.5" style="color:#5A80AB">kWh</span>
        </div>
        @if(count($trendVals))
        <div>{!! miniSpark($trendVals, '#3B82F6') !!}</div>
        @endif
    </div>

    {{-- KPI: CO₂ evitado --}}
    <div x-data="countUp({{ $co2Ton }}, 2)"
         x-intersect.once="start()"
         class="rounded-xl px-4 py-4 flex flex-col gap-2 relative overflow-hidden"
         style="background:rgba(9,21,41,0.88);border:1px solid #1A2D4A">
        <div class="text-xs font-medium uppercase tracking-widest" style="color:#8BADD4">CO₂ evitado</div>
        <div class="flex items-end gap-1">
            <span class="text-3xl font-black leading-none" style="color:#4ADE80" x-text="display"></span>
            <span class="text-sm mb-0.5" style="color:#5A80AB">ton CO₂</span>
        </div>
    </div>

    {{-- KPI: Capacidad instalada --}}
    <div x-data="countUp({{ $capacidadKwp }}, 1)"
         x-intersect.once="start()"
         class="rounded-xl px-4 py-4 flex flex-col gap-2 relative overflow-hidden"
         style="background:rgba(9,21,41,0.88);border:1px solid #1A2D4A">
        <div class="text-xs font-medium uppercase tracking-widest" style="color:#8BADD4">Capacidad inst.</div>
        <div class="flex items-end gap-1">
            <span class="text-3xl font-black leading-none" style="color:#A78BFA" x-text="display"></span>
            <span class="text-sm mb-0.5" style="color:#5A80AB">kWp</span>
        </div>
    </div>

    {{-- KPI: Plantas activas --}}
    <div x-data="countUp({{ $plantasAct }}, 0)"
         x-intersect.once="start()"
         class="rounded-xl px-4 py-4 flex flex-col gap-2 relative overflow-hidden"
         style="background:rgba(9,21,41,0.88);border:1px solid #1A2D4A">
        <div class="text-xs font-medium uppercase tracking-widest" style="color:#8BADD4">Plantas activas</div>
        <div class="flex items-end gap-1">
            <span class="text-3xl font-black leading-none" style="color:#E8F0FE" x-text="display"></span>
            <span class="text-sm mb-0.5" style="color:#5A80AB">/ {{ $plantasTot }}</span>
        </div>
    </div>

    {{-- KPI: Potencia PV ahora --}}
    <div x-data="countUp({{ $pvWattsLive }}, 0)"
         x-intersect.once="start()"
         class="rounded-xl px-4 py-4 flex flex-col gap-2 relative overflow-hidden"
         style="background:rgba(9,21,41,0.88);border:1px solid rgba(34,211,238,0.30);box-shadow:0 0 20px rgba(34,211,238,0.06)">
        <div class="text-xs font-medium uppercase tracking-widest" style="color:#8BADD4">Potencia PV ahora</div>
        <div class="flex items-center gap-2">
            <span class="relative flex" style="width:9px;height:9px">
                <span class="absolute inset-0 rounded-full animate-ping opacity-60"
                      style="background:#22D3EE;animation-duration:1.6s"></span>
                <span class="relative rounded-full" style="width:9px;height:9px;background:#22D3EE"></span>
            </span>
            <span class="text-3xl font-black leading-none" style="color:#34D399" x-text="display"></span>
            <span class="text-sm" style="color:#5A80AB">W</span>
        </div>
    </div>
</div>

{{-- ── Tarjetas por instalación ─────────────────────────────────────── --}}
<div class="px-4 sm:px-6 lg:px-8 py-4" style="border-bottom:1px solid #0F1D33">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold uppercase tracking-widest" style="color:#8BADD4">
            Instalaciones · estado en tiempo real
        </span>
        <span class="text-xs" style="color:#4A6A96">← desliza →</span>
    </div>
    <div class="flex gap-3 overflow-x-auto pb-2"
         style="scroll-snap-type:x mandatory;scrollbar-width:none;-ms-overflow-style:none">
        @foreach($instalaciones as $p)
        @php
        $sc  = $p['estado'] === 'online' ? '#34D399' : ($p['estado'] === 'alarm' ? '#F59E0B' : '#5A80AB');
        $gen = $p['pv_watts'] > 50;
        $pct = $p['prevision_kwh'] > 0 ? min(($p['gen_hoy_kwh'] / $p['prevision_kwh']) * 100, 100) : 0;
        $barColor = $pct >= 80 ? '#34D399' : ($pct >= 40 ? '#3B82F6' : '#1A2D4A');
        $initials = implode('', array_map(
            fn($w) => is_numeric($w) ? $w : strtoupper($w[0]),
            preg_split('/\s+/', trim($p['nombre']))
        ));
        @endphp
        <div class="flex-none snap-start rounded-xl flex flex-col gap-2 p-3"
             style="width:168px;background:rgba(9,21,41,0.92);border:1px solid {{ $p['estado'] === 'alarm' ? '#F59E0B30' : '#1A2D4A' }}">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm font-bold truncate" style="color:#E8F0FE">{{ $initials }}</span>
                <span class="w-2 h-2 rounded-full flex-none" style="background:{{ $sc }}"></span>
            </div>

            {{-- SOC gauge SVG --}}
            <div class="flex justify-center">
                {!! socGauge($p['soc_pct'], 84) !!}
            </div>

            {{-- PV watts --}}
            <div class="flex items-center justify-center gap-2">
                @if($gen)
                <span class="relative flex" style="width:8px;height:8px">
                    <span class="absolute inset-0 rounded-full animate-ping opacity-60"
                          style="background:#3B82F6;animation-duration:1.6s"></span>
                    <span class="relative rounded-full" style="width:8px;height:8px;background:#3B82F6"></span>
                </span>
                @else
                <span class="rounded-full" style="width:8px;height:8px;background:#1A2D4A"></span>
                @endif
                <span class="text-base font-black" style="color:{{ $gen ? '#60A5FA' : '#3D5A82' }}">
                    {{ $p['pv_watts'] >= 1000 ? number_format($p['pv_watts']/1000,1).' kW' : $p['pv_watts'].' W' }}
                </span>
            </div>

            <div style="height:1px;background:#0F1D33"></div>

            {{-- Hoy vs Previsión --}}
            <div class="flex justify-between items-baseline">
                <span class="text-xs font-medium" style="color:#5A80AB">Hoy</span>
                <span class="text-xs font-medium" style="color:#5A80AB">Prev.</span>
            </div>
            <div class="flex justify-between items-baseline -mt-1">
                <span class="text-sm font-bold" style="color:#60A5FA">
                    {{ $p['gen_hoy_kwh'] }}<span class="text-xs font-normal" style="color:#4A6A96"> kWh</span>
                </span>
                <span class="text-sm font-bold" style="color:#8BADD4">
                    ~{{ $p['prevision_kwh'] }}<span class="text-xs font-normal" style="color:#4A6A96"> kWh</span>
                </span>
            </div>

            {{-- Barra progreso --}}
            <div class="rounded-full overflow-hidden" style="height:4px;background:#0F1D33">
                <div class="h-full rounded-full" style="width:{{ round($pct) }}%;background:{{ $barColor }};transition:width .7s"></div>
            </div>

            <div class="text-xs font-medium uppercase tracking-widest text-center" style="color:#3D5A82">
                {{ $p['region'] }}
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Gráficos + Mapa ─────────────────────────────────────────────── --}}
<div class="px-4 sm:px-6 lg:px-8 py-4" style="border-bottom:1px solid #0F1D33"
     x-data="vrmCharts()" x-init="init()">

    {{-- Tabs range --}}
    <div class="flex items-center gap-4 mb-3 flex-wrap">
        <div class="flex gap-1">
            @foreach(['prod','cons','map'] as $tab)
            <button @click="activeTab='{{ $tab }}'"
                    :style="activeTab==='{{ $tab }}' ? 'color:#E8F0FE;border-color:#1A2D4A;background:#0F1D33' : 'color:#3D5A82;border-color:transparent;background:transparent'"
                    class="text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded border transition-colors">
                {{ ['prod'=>'Producción','cons'=>'Consumo','map'=>'Mapa'][$tab] }}
            </button>
            @endforeach
        </div>
        <template x-if="activeTab !== 'map'">
            <div class="flex gap-1 ml-auto">
                @foreach(['24h','72h','7d','14d','30d'] as $r)
                <button @click="setRange('{{ $r }}')"
                        :style="range==='{{ $r }}' ? 'color:#60A5FA;border-color:#3B82F630;background:#3B82F614' : 'color:#3D5A82;border-color:transparent;background:transparent'"
                        class="text-xs font-semibold px-2 py-1 rounded border transition-colors">
                    {{ $r }}
                </button>
                @endforeach
            </div>
        </template>
    </div>

    {{-- Chart producción --}}
    <div x-show="activeTab==='prod'" style="height:160px;position:relative">
        <template x-if="loading">
            <div class="absolute inset-0 flex items-end gap-1 px-3 pb-6">
                @for($i=0;$i<15;$i++)
                <div class="flex-1 rounded animate-pulse" style="height:{{ [35,55,45,70,60,80,50,75,65,85,55,70,40,60,50][$i] }}%;background:rgba(26,45,74,0.7)"></div>
                @endfor
            </div>
        </template>
        <canvas id="vrm-chart-prod" x-show="!loading" style="height:160px"></canvas>
    </div>

    {{-- Chart consumo --}}
    <div x-show="activeTab==='cons'" style="height:160px;position:relative">
        <template x-if="loading">
            <div class="absolute inset-0 flex items-end gap-1 px-3 pb-6">
                @for($i=0;$i<15;$i++)
                <div class="flex-1 rounded animate-pulse" style="height:{{ [40,60,50,75,65,80,55,70,45,85,60,70,50,65,55][$i] }}%;background:rgba(26,45,74,0.7)"></div>
                @endfor
            </div>
        </template>
        <canvas id="vrm-chart-cons" x-show="!loading" style="height:160px"></canvas>
    </div>

    {{-- Mapa --}}
    <div x-show="activeTab==='map'">
        <div id="vrm-map" style="height:260px;border-radius:8px;overflow:hidden;border:1px solid #1A2D4A"></div>
    </div>
</div>

{{-- ── Ticker ──────────────────────────────────────────────────────── --}}
<div class="px-4 py-2" style="background:#020611;border-top:1px solid #0A1525;overflow:hidden;position:relative">
    <div style="-webkit-mask-image:linear-gradient(to right,transparent,black 6%,black 94%,transparent)">
        <div class="ticker-track flex gap-6 whitespace-nowrap py-1">
            @php $tickerItems = array_merge($instalaciones, $instalaciones); @endphp
            @foreach($tickerItems as $p)
            @php
            $sc = $p['estado']==='online' ? '#34D399' : ($p['estado']==='alarm' ? '#F59E0B' : '#5A80AB');
            $initials = implode('', array_map(
                fn($w) => is_numeric($w) ? $w : strtoupper($w[0]),
                preg_split('/\s+/', trim($p['nombre']))
            ));
            @endphp
            <span class="inline-flex items-center gap-2 text-xs">
                <span class="w-2 h-2 rounded-full flex-none" style="background:{{ $sc }}"></span>
                <span class="font-semibold" style="color:#8BADD4">{{ $initials }}</span>
                @if($p['soc_pct'] !== null)
                <span style="color:#5A80AB">SOC <span style="color:{{ socColor($p['soc_pct']) }}">{{ $p['soc_pct'] }}%</span></span>
                @endif
                @if($p['pv_watts'] > 0)
                <span style="color:#5A80AB"><span style="color:#60A5FA">{{ $p['pv_watts'] >= 1000 ? number_format($p['pv_watts']/1000,1).' kW' : $p['pv_watts'].' W' }}</span> PV</span>
                @endif
                <span style="color:#0F1D33">·</span>
            </span>
            @endforeach
        </div>
    </div>
</div>

@endif {{-- /error --}}
</div>

@push('head')
<style>
.ticker-track { animation: ticker-scroll 60s linear infinite; }
.ticker-track:hover { animation-play-state: paused; }
@keyframes ticker-scroll {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── countUp Alpine component ──────────────────────────────────────────
function countUp(target, decimals = 0, ms = 1800) {
  return {
    target, decimals, ms,
    val: 0,
    get display() { return this.val.toFixed(this.decimals) },
    start() {
      const t0 = performance.now()
      const tick = (now) => {
        const p = Math.min((now - t0) / this.ms, 1)
        const e = 1 - Math.pow(1 - p, 3)
        this.val = parseFloat((e * this.target).toFixed(this.decimals))
        if (p < 1) requestAnimationFrame(tick)
        else this.val = this.target
      }
      requestAnimationFrame(tick)
    }
  }
}

// ── VRM Charts + Map Alpine component ────────────────────────────────
function vrmCharts() {
  return {
    activeTab: 'prod',
    range: '24h',
    loading: false,
    chartProd: null,
    chartCons: null,
    map: null,
    markers: @json($mapMarkers ?? []),

    init() {
      this.$nextTick(() => {
        this.initMap()
        this.loadCharts()
      })
    },

    initMap() {
      const m = L.map('vrm-map', { zoomControl: true, scrollWheelZoom: false })
      L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO', maxZoom: 18
      }).addTo(m)

      const bounds = []
      this.markers.forEach(mk => {
        const color = mk.estado === 'online' ? '#34D399' : mk.estado === 'alarm' ? '#F59E0B' : '#5A80AB'
        const icon = L.divIcon({
          html: `<div style="width:12px;height:12px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.3);box-shadow:0 0 8px ${color}80"></div>`,
          className: '', iconSize: [12,12], iconAnchor: [6,6]
        })
        L.marker([mk.lat, mk.lng], { icon }).bindPopup(
          `<div style="color:#E8F0FE;background:#0D1E3A;padding:8px;border-radius:6px;font-size:13px"><b>${mk.nombre}</b></div>`
        ).addTo(m)
        bounds.push([mk.lat, mk.lng])
      })

      if (bounds.length > 1) m.fitBounds(bounds, { padding: [30,30] })
      else if (bounds.length === 1) m.setView(bounds[0], 10)
      else m.setView([-52.0, -71.0], 6)

      this.map = m
      this.$watch('activeTab', v => { if (v === 'map') setTimeout(() => m.invalidateSize(), 50) })
    },

    async loadCharts() {
      this.loading = true
      try {
        const res = await fetch(`/api/vrm/chart?range=${this.range}`)
        if (!res.ok) throw new Error('VRM API error')
        const data = await res.json()

        const labels = data.production.map(p => this.fmtLabel(p.ts))
        const prodData = data.production.map(p => p.kwh)
        const consData = data.consumption.map(p => p.kwh)

        this.renderChart('vrm-chart-prod', 'chartProd', labels, prodData, '#3B82F6', '#3B82F640')
        this.renderChart('vrm-chart-cons', 'chartCons', labels, consData, '#F59E0B', '#F59E0B40')
      } catch(e) {
        console.warn('VRM chart error:', e)
      } finally {
        this.loading = false
      }
    },

    renderChart(id, prop, labels, data, color, fill) {
      const canvas = document.getElementById(id)
      if (!canvas) return
      if (this[prop]) this[prop].destroy()
      this[prop] = new Chart(canvas, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            data,
            borderColor: color,
            backgroundColor: fill,
            borderWidth: 1.5,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: false }, tooltip: {
            backgroundColor: '#0D1E3A', titleColor: '#8BADD4', bodyColor: '#E8F0FE',
            borderColor: '#1A2D4A', borderWidth: 1,
          }},
          scales: {
            x: { ticks: { color: '#3D5A82', font: { size: 10 } }, grid: { color: '#0F1D33' } },
            y: { ticks: { color: '#3D5A82', font: { size: 10 } }, grid: { color: '#0F1D33' } },
          }
        }
      })
    },

    setRange(r) {
      this.range = r
      this.loadCharts()
    },

    fmtLabel(tsMs) {
      const d = new Date(tsMs - 3 * 3600000)
      if (['24h','72h'].includes(this.range))
        return d.getUTCHours().toString().padStart(2,'0') + ':00'
      return `${d.getUTCDate()}/${(d.getUTCMonth()+1).toString().padStart(2,'0')}`
    }
  }
}
</script>
@endpush
