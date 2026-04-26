@extends('layouts.app')
@section('title', 'Experiencia — Enertecs')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        #mapa-exp .leaflet-container { background: #04091A; }
        #mapa-exp .leaflet-tile-pane { filter: brightness(0.85) saturate(0.7); }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(13,148,136,0.25); border-radius:2px; }
    </style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const PROYECTOS_DATA = @json($proyectos);

function experienciaPage() {
    return {
        activeTab: 'map',
        selected: null,
        expandedCat: null,
        map: null,
        markers: [],

        get categorias() {
            const map = new Map()
            for (const p of PROYECTOS_DATA) {
                const key = p.categoria ?? 'Sin especificar'
                if (!map.has(key)) map.set(key, [])
                map.get(key).push(p)
            }
            return Array.from(map.entries())
                .sort((a, b) => b[1].length - a[1].length)
                .map(([nombre, proyectos]) => ({ nombre, proyectos }))
        },

        selectProject(p) {
            this.selected = p
            if (p.lat && p.lng && this.map) {
                this.map.setView([p.lat, p.lng], 10)
            }
        },

        closeModal() { this.selected = null },

        initMap() {
            if (this.map) return
            const el = document.getElementById('mapa-exp')
            if (!el) return
            this.map = L.map(el, { zoomControl: false }).setView([-53.15, -70.91], 6)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '© CARTO', maxZoom: 18
            }).addTo(this.map)
            L.control.zoom({ position: 'bottomright' }).addTo(this.map)

            const icon = L.divIcon({
                className: '',
                html: '<div style="width:10px;height:10px;background:#0D9488;border-radius:50%;border:2px solid rgba(255,255,255,0.7);box-shadow:0 0 6px rgba(13,148,136,0.6)"></div>',
                iconSize: [10, 10],
                iconAnchor: [5, 5],
            })

            PROYECTOS_DATA.filter(p => p.lat && p.lng).forEach(p => {
                const m = L.marker([p.lat, p.lng], { icon }).addTo(this.map)
                m.bindPopup(`<b style="color:#fff">${p.nombre}</b><br><span style="color:rgba(255,255,255,0.6);font-size:12px">${p.cliente} · ${p['año']}</span>`, {
                    className: 'dark-popup'
                })
                m.on('click', () => this.selectProject(p))
                this.markers.push({ marker: m, proyecto: p })
            })

            setTimeout(() => this.map.invalidateSize(), 150)
        }
    }
}
</script>
<style>
.dark-popup .leaflet-popup-content-wrapper {
    background: #0d1e3a;
    border: 1px solid rgba(13,148,136,0.3);
    color: white;
    border-radius: 8px;
}
.dark-popup .leaflet-popup-tip { background: #0d1e3a; }
</style>
@endpush

@section('content')
<div class="bg-[#04091A] overflow-hidden" style="height:calc(100vh - 64px)"
     x-data="experienciaPage()" x-init="initMap()">

    {{-- ── MOBILE: tabs ── --}}
    <div class="md:hidden flex border-b bg-[#04091A]" style="border-color:rgba(255,255,255,0.08)">
        <button @click="activeTab='map'"
                :class="activeTab==='map' ? 'text-[#0D9488] border-[#0D9488]' : 'text-white/40 border-transparent'"
                class="flex-1 py-3 text-xs font-bold uppercase tracking-widest transition-colors border-b-2">
            Mapa
        </button>
        <button @click="activeTab='list'"
                :class="activeTab==='list' ? 'text-[#0D9488] border-[#0D9488]' : 'text-white/40 border-transparent'"
                class="flex-1 py-3 text-xs font-bold uppercase tracking-widest transition-colors border-b-2">
            Obras <span class="ml-1.5 font-normal text-white/40">· {{ $proyectos->count() }}</span>
        </button>
    </div>

    {{-- ── MOBILE: mapa ── --}}
    <div x-show="activeTab==='map'" class="md:hidden relative" style="height:calc(100% - 44px)">
        <div id="mapa-exp-mobile" class="w-full h-full"></div>
    </div>

    {{-- ── MOBILE: lista ── --}}
    <div x-show="activeTab==='list'" class="md:hidden overflow-y-auto bg-[#04091A] sidebar-scroll"
         style="height:calc(100% - 44px)">
        <div class="px-4 py-3" style="border-bottom:1px solid rgba(13,148,136,0.10)">
            <p class="text-white/30 text-[10px] uppercase tracking-widest">Magallanes · Aysén · Otras</p>
        </div>
        <template x-for="group in categorias" :key="group.nombre">
            <div>
                <button @click="expandedCat = expandedCat === group.nombre ? null : group.nombre"
                        class="w-full flex items-center justify-between px-4 py-3 transition-colors hover:bg-white/5"
                        style="border-bottom:1px solid rgba(255,255,255,0.05)">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#0D9488] flex-shrink-0"></div>
                        <span class="text-white/80 text-xs font-bold uppercase tracking-wide" x-text="group.nombre"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[#0D9488]/60 text-[10px] font-bold" x-text="group.proyectos.length"></span>
                        <svg :class="expandedCat === group.nombre ? 'rotate-180' : ''" class="w-3 h-3 text-white/30 transition-transform duration-200"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                </button>
                <div x-show="expandedCat === group.nombre" class="bg-[#04091A]">
                    <template x-for="p in group.proyectos" :key="p.id">
                        <button @click="selectProject(p)"
                                :class="selected?.id === p.id ? 'border-l-[#0D9488] bg-[#0D9488]/8' : 'border-l-transparent hover:bg-white/5'"
                                class="w-full text-left px-6 py-3 border-b border-white/5 border-l-2 transition-colors">
                            <div class="text-white font-bold text-xs leading-snug mb-0.5" x-text="p.nombre"></div>
                            <div class="text-[#0D9488] text-[10px] font-semibold" x-text="p.cliente"></div>
                            <div class="text-white/30 text-[10px] mt-0.5" x-text="p.año"></div>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- ── DESKTOP: mapa full-screen + sidebar superpuesto ── --}}
    <div class="hidden md:block h-full relative">

        {{-- Mapa de fondo --}}
        <div id="mapa-exp" class="absolute inset-0 z-0"></div>

        {{-- Sidebar translúcido --}}
        <aside class="absolute left-0 top-0 bottom-0 flex flex-col overflow-hidden z-30"
               style="width:320px;background:linear-gradient(to right,rgba(4,9,26,0.97) 0%,rgba(4,9,26,0.92) 100%);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-right:1px solid rgba(255,255,255,0.08)">

            {{-- Línea acento izquierda --}}
            <div aria-hidden="true" class="absolute left-0 pointer-events-none"
                 style="top:12%;bottom:12%;width:2px;background:linear-gradient(180deg,transparent,#0D9488 30%,#0D9488 70%,transparent)"></div>

            {{-- Header --}}
            <div class="px-6 pt-6 pb-5 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.08)">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-white/60 text-[13px] font-semibold uppercase tracking-widest">Trayectoria</span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md text-[#0D9488]"
                          style="background:rgba(13,148,136,0.10);border:1px solid rgba(13,148,136,0.25)">
                        {{ $proyectos->count() }}+
                    </span>
                </div>
                <h2 class="text-white font-black text-[26px] leading-tight mb-1.5"
                    style="letter-spacing:-0.025em">Experiencia</h2>
                <p class="text-white/50 text-[13px] font-medium">Magallanes · Aysén · Otras</p>
            </div>

            {{-- Lista de proyectos agrupada por categoría --}}
            <div class="flex-1 overflow-y-auto py-2 sidebar-scroll">
                <template x-for="(group, gi) in categorias" :key="group.nombre">
                    <div :class="gi > 0 ? 'mt-1' : ''">
                        <div class="flex items-center gap-2 px-6 pt-4 pb-1.5">
                            <div class="w-[5px] h-[5px] rounded-full bg-[#0D9488] flex-shrink-0" style="opacity:0.6" aria-hidden="true"></div>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-white/50" x-text="group.nombre"></span>
                            <span class="ml-auto text-[11px] font-bold text-white/35 tabular-nums" x-text="group.proyectos.length"></span>
                        </div>
                        <template x-for="p in group.proyectos" :key="p.id">
                            <button @click="selectProject(p)"
                                    :class="selected?.id === p.id ? 'border-l-[#0D9488] bg-white/5' : 'border-l-transparent hover:border-l-[#0D9488]/40 hover:bg-white/5'"
                                    class="w-full text-left px-6 py-2.5 border-l-2 transition-all duration-150 cursor-pointer">
                                <div :class="selected?.id === p.id ? 'text-white' : 'text-white/70'"
                                     class="text-[12px] font-semibold leading-snug mb-0.5 transition-colors"
                                     x-text="p.nombre"></div>
                                <div class="text-[12px] font-medium text-[#0D9488] leading-tight" x-text="p.cliente"></div>
                                <div class="text-[11px] text-white/35 mt-0.5 tabular-nums" x-text="p.año"></div>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </aside>
    </div>

    {{-- Modal proyecto seleccionado --}}
    <div x-show="selected !== null"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="closeModal()">
        <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
        <div class="relative rounded-xl p-6 max-w-md w-full z-10"
             style="background:#0d1e3a;border:1px solid rgba(13,148,136,0.25)">
            <button @click="closeModal()"
                    class="absolute top-4 right-4 text-white/40 hover:text-white transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <template x-if="selected">
                <div>
                    <p class="text-[#0D9488] text-[10px] font-bold uppercase tracking-widest mb-1"
                       x-text="selected.categoria"></p>
                    <h3 class="text-white font-black text-lg leading-tight mb-1" x-text="selected.nombre"></h3>
                    <p class="text-white/50 text-sm mb-4">
                        <span x-text="selected.cliente"></span>
                        <span class="text-white/25"> · </span>
                        <span x-text="selected.año"></span>
                    </p>
                    <template x-if="selected.descripcion">
                        <p class="text-white/60 text-sm leading-relaxed" x-text="selected.descripcion"></p>
                    </template>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
// Inicializar el mapa de desktop una vez cargado Leaflet
document.addEventListener('DOMContentLoaded', () => {
    // Alpine ya inicializó el mapa desktop via x-init=initMap()
    // El mapa mobile (tab) se inicializa separado si existe el elemento
})
</script>
@endsection
