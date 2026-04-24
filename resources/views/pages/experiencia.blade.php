@extends('layouts.app')
@section('title', 'Experiencia — Enertecs')
@section('content')
<section class="min-h-screen bg-[#0a1628] py-12">
  <div class="max-w-7xl mx-auto px-6">
    <h1 class="text-white font-black text-3xl mb-2" style="font-family: var(--font-heading)">Nuestra Experiencia</h1>
    <p class="text-white/40 text-sm mb-8">Proyectos ejecutados en la Patagonia chilena y más allá.</p>

    {{-- Filtros por categoría --}}
    <div class="flex flex-wrap gap-2 mb-8" x-data="{ cat: '' }" id="filtros">
        <button @click="cat = ''; filtrarMapa('')" :class="cat === '' ? 'bg-[#0067FF] text-white' : 'bg-white/5 text-white/60 hover:text-white'"
            class="px-4 py-2 rounded-lg text-xs font-bold tracking-widest transition-colors">TODOS</button>
        @foreach($proyectos->pluck('categoria')->unique() as $cat)
        <button @click="cat = '{{ $cat }}'; filtrarMapa('{{ $cat }}')" :class="cat === '{{ $cat }}' ? 'bg-[#0067FF] text-white' : 'bg-white/5 text-white/60 hover:text-white'"
            class="px-4 py-2 rounded-lg text-xs font-bold tracking-widest transition-colors">{{ strtoupper($cat) }}</button>
        @endforeach
    </div>

    {{-- Mapa Leaflet --}}
    <div id="mapa" class="w-full h-80 rounded-2xl overflow-hidden mb-10 border border-white/5"></div>

    {{-- Lista de proyectos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="lista-proyectos">
        @foreach($proyectos as $p)
        <div class="proyecto-card bg-[#0d1e3a] border border-white/5 rounded-2xl p-5"
             data-cat="{{ $p->categoria }}">
            <p class="text-[10px] font-bold text-[#0067FF] uppercase tracking-widest mb-1">{{ $p->categoria }}</p>
            <p class="text-white font-black text-sm">{{ $p->nombre }}</p>
            <p class="text-white/40 text-xs mt-1">{{ $p->cliente }} · {{ $p->año }}</p>
            @if($p->descripcion)
                <p class="text-white/50 text-xs mt-2 line-clamp-2">{{ $p->descripcion }}</p>
            @endif
        </div>
        @endforeach
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
const proyectos = @json($proyectos);
const mapa = L.map('mapa', { zoomControl: true }).setView([-53.15, -70.91], 6);
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '© CARTO', maxZoom: 18
}).addTo(mapa);

const icon = L.divIcon({ className:'', html:'<div style="width:10px;height:10px;background:#0067FF;border-radius:50%;border:2px solid white"></div>' });
const markers = proyectos.filter(p => p.lat && p.lng).map(p => {
    const m = L.marker([p.lat, p.lng], { icon }).addTo(mapa);
    m.bindPopup(`<b>${p.nombre}</b><br>${p.cliente} · ${p.año}`);
    m.categoria = p.categoria;
    return m;
});

function filtrarMapa(cat) {
    markers.forEach(m => {
        const visible = !cat || m.categoria === cat;
        visible ? m.addTo(mapa) : mapa.removeLayer(m);
    });
    document.querySelectorAll('.proyecto-card').forEach(el => {
        el.style.display = (!cat || el.dataset.cat === cat) ? '' : 'none';
    });
}
</script>
@endpush
