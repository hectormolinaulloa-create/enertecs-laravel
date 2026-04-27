@extends('layouts.app')
@section('title', 'Experiencia — Enertecs')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        #mapa-exp .leaflet-container { background: #04091A; }
        #mapa-exp .leaflet-tile-pane { filter: none; }
        #mapa-exp .leaflet-bottom.leaflet-right { bottom: 92px; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(13,148,136,0.25); border-radius:2px; }
    </style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const PROYECTOS_DATA = @json($proyectos);

const CAPAS_MAPA = {
    dark:      { nombre: 'Oscuro',       icono: '●', url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',                                                      attr: '© CARTO',        opts: {} },
    nolabels:  { nombre: 'Sin etiquetas',icono: '◌', url: 'https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png',                                                 attr: '© CARTO',        opts: {} },
    satellite: { nombre: 'Satélite',     icono: '◈', url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',                       attr: '© Esri',         opts: { maxZoom: 17 } },
    topo:      { nombre: 'Topográfico',  icono: '◬', url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',                                                                   attr: '© OpenTopoMap',  opts: {} },
    voyager:   { nombre: 'Claro',        icono: '○', url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',                                           attr: '© CARTO',        opts: {} },
}

function experienciaPage() {
    return {
        activeTab: 'map',
        selected: null,
        expandedCat: null,
        map: null,
        markers: [],
        tileLayer: null,
        currentLayer: 'voyager',
        layerPickerOpen: false,

        get capas() { return Object.entries(CAPAS_MAPA).map(([key, v]) => ({ key, ...v })) },

        get categorias() {
            const map = new Map()
            for (const p of PROYECTOS_DATA) {
                const key = p.comuna ?? 'Sin especificar'
                if (!map.has(key)) map.set(key, [])
                map.get(key).push(p)
            }
            return Array.from(map.entries())
                .sort((a, b) => a[0].localeCompare(b[0], 'es'))
                .map(([nombre, proyectos]) => ({ nombre, proyectos }))
        },

        selectProject(p) {
            this.selected = p
            if (p.lat && p.lng && this.map) {
                const zoom = 15
                if (window.innerWidth >= 768) {
                    // Desplazar centro hacia el sur para que el pin quede sobre el modal
                    const offset = window.innerHeight * 0.1 + 44
                    const pinPx = this.map.project([p.lat, p.lng], zoom)
                    const center = this.map.unproject(pinPx.add([0, offset]), zoom)
                    this.map.setView(center, zoom)
                } else {
                    this.map.setView([p.lat, p.lng], zoom)
                }
            }
        },

        closeModal() { this.selected = null },

        switchLayer(key) {
            if (!this.map) return
            if (this.tileLayer) this.map.removeLayer(this.tileLayer)
            const c = CAPAS_MAPA[key]
            this.tileLayer = L.tileLayer(c.url, { attribution: c.attr, maxZoom: 18, ...c.opts })
            this.tileLayer.addTo(this.map)
            this.tileLayer.bringToBack()
            this.currentLayer = key
            this.layerPickerOpen = false
        },

        initMap() {
            if (this.map) return
            const el = document.getElementById('mapa-exp')
            if (!el) return
            this.map = L.map(el, { zoomControl: false }).setView([-53.15, -70.91], 6)
            this.switchLayer('voyager')
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
        <div class="px-4 py-3" style="border-bottom:1px solid rgba(13,148,136,0.15)">
            <p class="text-white/60 text-[10px] uppercase tracking-widest">Magallanes · Aysén · Otras</p>
        </div>
        <template x-for="group in categorias" :key="group.nombre">
            <div>
                <button @click="expandedCat = expandedCat === group.nombre ? null : group.nombre"
                        class="w-full flex items-center justify-between px-4 py-3 transition-colors hover:bg-white/5"
                        style="border-bottom:1px solid rgba(255,255,255,0.05)">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-[#0D9488] flex-shrink-0"></div>
                        <span class="text-white/90 text-[14px] font-extrabold uppercase tracking-wide" x-text="group.nombre"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[#0D9488]/90 text-[12px] font-bold" x-text="group.proyectos.length"></span>
                        <svg :class="expandedCat === group.nombre ? 'rotate-180' : ''" class="w-3 h-3 text-white/55 transition-transform duration-200"
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
                            <div class="text-white/60 text-[10px] mt-0.5" x-text="p.año"></div>
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

        {{-- Selector de capas --}}
        <div class="absolute z-20" style="top:12px;right:12px" @click.outside="layerPickerOpen=false">

            {{-- Botón toggle --}}
            <button @click="layerPickerOpen = !layerPickerOpen"
                    :style="layerPickerOpen ? 'background:rgba(13,148,136,0.2);border-color:rgba(13,148,136,0.6)' : 'background:rgba(4,9,26,0.82);border-color:rgba(255,255,255,0.12)'"
                    style="display:flex;align-items:center;gap:7px;padding:7px 11px;border-radius:8px;border:1px solid;backdrop-filter:blur(12px);cursor:pointer;transition:all 0.15s;color:#fff">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;opacity:0.8">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                    <polyline points="2 17 12 22 22 17"/>
                    <polyline points="2 12 12 17 22 12"/>
                </svg>
                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;opacity:0.85"
                      x-text="CAPAS_MAPA[currentLayer].nombre"></span>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     :style="layerPickerOpen ? 'transform:rotate(180deg)' : ''"
                     style="opacity:0.5;transition:transform 0.15s">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="layerPickerOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position:absolute;top:calc(100% + 6px);right:0;min-width:160px;border-radius:10px;overflow:hidden;border:1px solid rgba(255,255,255,0.10);background:rgba(4,9,26,0.95);backdrop-filter:blur(16px);box-shadow:0 8px 32px rgba(0,0,0,0.6)">
                <template x-for="capa in capas" :key="capa.key">
                    <button @click="switchLayer(capa.key)"
                            :style="currentLayer === capa.key
                                ? 'background:rgba(13,148,136,0.12);color:#0D9488'
                                : 'color:rgba(255,255,255,0.75)'"
                            style="width:100%;text-align:left;display:flex;align-items:center;gap:10px;padding:9px 14px;border:none;cursor:pointer;transition:all 0.12s;border-bottom:1px solid rgba(255,255,255,0.05)"
                            onmouseover="if(this.style.background !== 'rgba(13,148,136,0.12)') this.style.background='rgba(255,255,255,0.05)'"
                            onmouseout="if(this.style.background !== 'rgba(13,148,136,0.12)') this.style.background='transparent'">
                        <span style="font-size:14px;line-height:1;width:16px;text-align:center;flex-shrink:0" x-text="capa.icono"></span>
                        <span style="font-size:11px;font-weight:600;letter-spacing:0.04em" x-text="capa.nombre"></span>
                        <svg x-show="currentLayer === capa.key" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="margin-left:auto;flex-shrink:0">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>

        {{-- Sidebar translúcido --}}
        <aside class="absolute left-0 top-0 bottom-0 flex flex-col overflow-hidden z-30"
               style="width:320px;background:linear-gradient(to right,rgba(4,9,26,0.97) 0%,rgba(4,9,26,0.92) 100%);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-right:1px solid rgba(255,255,255,0.08)">

            {{-- Línea acento izquierda --}}
            <div aria-hidden="true" class="absolute left-0 pointer-events-none"
                 style="top:12%;bottom:12%;width:2px;background:linear-gradient(180deg,transparent,#0D9488 30%,#0D9488 70%,transparent)"></div>

            {{-- Header --}}
            <div class="px-6 pt-6 pb-5 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.08)">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-white/85 text-[13px] font-semibold uppercase tracking-widest">Trayectoria</span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md text-[#0D9488]"
                          style="background:rgba(13,148,136,0.10);border:1px solid rgba(13,148,136,0.25)">
                        {{ $proyectos->count() }}+
                    </span>
                </div>
                <h2 class="text-white font-black text-[26px] leading-tight mb-1.5"
                    style="letter-spacing:-0.025em">Experiencia</h2>
                <p class="text-white/70 text-[13px] font-medium">Magallanes · Aysén · Otras</p>
            </div>

            {{-- Lista de proyectos agrupada por categoría --}}
            <div class="flex-1 overflow-y-auto py-2 sidebar-scroll">
                <template x-for="(group, gi) in categorias" :key="group.nombre">
                    <div :class="gi > 0 ? 'mt-1' : ''">
                        <div class="flex items-center gap-2.5 px-6 pt-5 pb-2">
                            <div class="w-[6px] h-[6px] rounded-full bg-[#0D9488] flex-shrink-0" aria-hidden="true"></div>
                            <span class="text-[13px] font-extrabold uppercase tracking-wider text-white/90" x-text="group.nombre"></span>
                            <span class="ml-auto text-[12px] font-bold text-white/65 tabular-nums" x-text="group.proyectos.length"></span>
                        </div>
                        <template x-for="p in group.proyectos" :key="p.id">
                            <button @click="selectProject(p)"
                                    :class="selected?.id === p.id ? 'border-l-[#0D9488] bg-white/5' : 'border-l-transparent hover:border-l-[#0D9488]/40 hover:bg-white/5'"
                                    class="w-full text-left px-6 py-2.5 border-l-2 transition-all duration-150 cursor-pointer">
                                <div :class="selected?.id === p.id ? 'text-white' : 'text-white/90'"
                                     class="text-[12px] font-semibold leading-snug mb-0.5 transition-colors"
                                     x-text="p.nombre"></div>
                                <div class="text-[12px] font-medium text-[#0D9488] leading-tight" x-text="p.cliente"></div>
                                <div class="text-[11px] text-white/60 mt-0.5 tabular-nums" x-text="p.año"></div>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </aside>
    </div>

    {{-- Modal proyecto seleccionado --}}
    <div x-show="selected !== null"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 md:left-[320px] z-50 flex items-end"
         @click.self="closeModal()">

{{-- Card: bottom-sheet en mobile / posicionado geométricamente en desktop --}}
        <div id="modal-wrapper" class="relative w-full z-10 overflow-hidden"
             style="border-radius:20px 20px 0 0;background:linear-gradient(160deg,#0b1e35 0%,#071526 50%,#04101e 100%)"
             :style="'box-shadow:0 -2px 0 0 rgba(13,148,136,0.35),0 0 0 1px rgba(13,148,136,0.1),0 -40px 80px rgba(0,0,0,0.7)'">

            {{-- Desktop: posición geométrica + bordes uniformes --}}
            <style>
                /* Tablet (768–1106px): centrado en el área del mapa */
                @media (min-width: 768px) and (max-width: 1106px) {
                    #modal-wrapper {
                        position: absolute !important;
                        top: 10% !important;
                        left: 50% !important;
                        right: auto !important;
                        transform: translateX(-50%) !important;
                        width: calc(100% - 24px) !important;
                        max-width: 440px !important;
                        border-radius: 18px !important;
                        background: linear-gradient(160deg,#0b1e35 0%,#071526 50%,#04101e 100%) !important;
                        box-shadow: 0 0 0 1px rgba(13,148,136,0.20), 0 24px 60px rgba(0,0,0,0.70), 0 0 40px rgba(13,148,136,0.08) !important;
                    }
                }
                /* Desktop (≥1107px): vértice sup-der al 20% de la diagonal centro→esquina */
                @media (min-width: 1107px) {
                    #modal-wrapper {
                        position: absolute !important;
                        top: calc(40vh + 38px) !important;
                        right: calc(40vw - 128px) !important;
                        width: 460px !important;
                        border-radius: 18px !important;
                        background: linear-gradient(160deg,#0b1e35 0%,#071526 50%,#04101e 100%) !important;
                        box-shadow: 0 0 0 1px rgba(13,148,136,0.20), 0 24px 60px rgba(0,0,0,0.70), 0 0 40px rgba(13,148,136,0.08) !important;
                    }
                }
            </style>

            <template x-if="selected">
                <div id="modal-card">

                    {{-- Línea de acento superior (teal degradada) --}}
                    <div style="height:2px;background:linear-gradient(90deg,#0D9488 0%,rgba(13,148,136,0.4) 55%,transparent 100%)"></div>

                    {{-- HEADER --}}
                    <div style="padding:26px 28px 20px">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
                            <div style="flex:1;min-width:0">
                                {{-- Categoría + año --}}
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                    <span style="font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:0.2em;color:#0D9488" x-text="selected.categoria"></span>
                                    <span style="width:1px;height:10px;background:rgba(255,255,255,0.12);display:inline-block"></span>
                                    <span style="font-family:monospace;font-size:11px;letter-spacing:0.1em;color:rgba(255,255,255,0.68)" x-text="selected.año"></span>
                                </div>
                                {{-- Nombre del proyecto --}}
                                <h3 style="color:#fff;font-weight:900;line-height:1.2;letter-spacing:-0.025em;font-size:clamp(1.1rem,3vw,1.4rem);margin:0"
                                    x-text="selected.nombre"></h3>
                            </div>
                            {{-- Botón cerrar --}}
                            <button @click="closeModal()"
                                    style="flex-shrink:0;width:30px;height:30px;border-radius:50%;border:1px solid rgba(255,255,255,0.15);background:transparent;display:flex;align-items:center;justify-content:center;cursor:pointer;color:rgba(255,255,255,0.60);transition:all 0.15s"
                                    onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='rgba(255,255,255,0.95)'"
                                    onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,0.60)'">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- SEPARADOR --}}
                    <div style="height:1px;background:linear-gradient(90deg,rgba(13,148,136,0.3) 0%,rgba(255,255,255,0.06) 50%,transparent 100%)"></div>

                    {{-- GRILLA DE DATOS: cliente / mandante / región / comuna --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:rgba(255,255,255,0.04)">
                        <div style="padding:14px 28px;background:#060f1d">
                            <p style="font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.17em;color:rgba(13,148,136,0.9);margin:0 0 5px">Cliente</p>
                            <p style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.8);margin:0;line-height:1.3" x-text="selected.cliente"></p>
                        </div>
                        <div style="padding:14px 28px;background:#060f1d">
                            <p style="font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.17em;color:rgba(13,148,136,0.9);margin:0 0 5px">Mandante</p>
                            <p style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.8);margin:0;line-height:1.3"
                               x-text="(selected.mandante && selected.mandante !== selected.cliente) ? selected.mandante : selected.cliente"></p>
                        </div>
                        <div style="padding:14px 28px;background:#060f1d">
                            <p style="font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.17em;color:rgba(13,148,136,0.9);margin:0 0 5px">Región</p>
                            <p style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.8);margin:0"
                               x-text="selected.region ? 'Región ' + selected.region : '—'"></p>
                        </div>
                        <div style="padding:14px 28px;background:#060f1d">
                            <p style="font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.17em;color:rgba(13,148,136,0.9);margin:0 0 5px">Comuna</p>
                            <p style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.8);margin:0" x-text="selected.comuna || '—'"></p>
                        </div>
                    </div>

                    {{-- SEPARADOR --}}
                    <div style="height:1px;background:rgba(255,255,255,0.05)"></div>

                    {{-- DESCRIPCIÓN + DIRECCIÓN --}}
                    <div style="padding:20px 28px">
                        <template x-if="selected.descripcion">
                            <p style="font-size:13px;color:rgba(255,255,255,0.75);line-height:1.65;margin:0 0 14px" x-text="selected.descripcion"></p>
                        </template>
                        <template x-if="selected.direccion">
                            <div style="display:flex;align-items:flex-start;gap:8px">
                                <svg style="width:11px;height:11px;flex-shrink:0;margin-top:2px;color:#0D9488;opacity:0.9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span style="font-size:11px;color:rgba(255,255,255,0.65);line-height:1.6" x-text="selected.direccion"></span>
                            </div>
                        </template>
                    </div>

                    {{-- FOOTER: coordenadas --}}
                    <template x-if="selected.lat && selected.lng">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 28px;border-top:1px solid rgba(255,255,255,0.05);background:rgba(13,148,136,0.04)">
                            <span style="font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:0.22em;color:rgba(255,255,255,0.50)">Coordenadas</span>
                            <span style="font-family:monospace;font-size:11px;color:#0D9488;letter-spacing:0.04em"
                                  x-text="selected.lat.toFixed(4) + '°,  ' + selected.lng.toFixed(4) + '°'"></span>
                        </div>
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
