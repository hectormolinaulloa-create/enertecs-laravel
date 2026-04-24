@extends('layouts.app')
@section('title', 'Enertecs — Ingeniería Eléctrica en Patagonia')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    function heroSection() {
        return {
            init() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
                const iframe = document.getElementById('hero-yt-iframe')
                const container = document.getElementById('hero-yt')
                if (!iframe || !container) return
                iframe.src = 'https://www.youtube.com/embed/tpwOV-doUCc' +
                    '?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&rel=0' +
                    '&iv_load_policy=3&playlist=tpwOV-doUCc&enablejsapi=1'
                iframe.addEventListener('load', () => {
                    container.style.opacity = '1'
                }, { once: true })
            }
        }
    }

    function parallaxDivider() {
        return {
            init() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
                let ticking = false
                const update = () => {
                    const el = this.$refs.img
                    if (!el) return
                    const rect = el.parentElement.getBoundingClientRect()
                    const offset = (rect.top + rect.height / 2 - window.innerHeight / 2) * 0.25
                    el.style.transform = `translateY(${offset}px)`
                    ticking = false
                }
                update()
                window.addEventListener('scroll', () => {
                    if (!ticking) { ticking = true; requestAnimationFrame(update) }
                }, { passive: true })
            }
        }
    }
    </script>
@endpush

@section('content')

{{-- Hero --}}
<section class="relative min-h-screen flex items-center overflow-hidden pt-16"
         x-data="heroSection()" x-init="init()">

    {{-- Fondo: video YouTube con fallback foto --}}
    <div class="absolute inset-0 z-0 bg-[#04091A]">
        {{-- Fallback foto (visible siempre; video la tapa si carga) --}}
        <img src="/images/tablero.jpg" alt=""
             class="absolute inset-0 w-full h-full object-cover object-center opacity-60"
             style="pointer-events:none">
        {{-- Iframe YouTube sin controles --}}
        <div id="hero-yt" class="absolute inset-0 pointer-events-none overflow-hidden opacity-0 transition-opacity duration-1000">
            <iframe id="hero-yt-iframe"
                    allow="autoplay; encrypted-media"
                    style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                           width:177.78vh;min-width:100%;height:56.25vw;min-height:100%;
                           pointer-events:none;border:0"
                    title="Video corporativo Enertecs">
            </iframe>
        </div>
    </div>

    {{-- Overlay degradado (más oscuro izquierda, más claro derecha) --}}
    <div aria-hidden="true" class="absolute inset-0 z-10 pointer-events-none"
         style="background:linear-gradient(to right,rgba(4,9,26,0.93) 0%,rgba(4,9,26,0.78) 60%,rgba(4,9,26,0.45) 100%)"></div>

    {{-- Línea acento vertical izquierda --}}
    <div aria-hidden="true"
         class="absolute left-0 top-[20%] bottom-[20%] w-[3px] hidden lg:block z-20"
         style="background:linear-gradient(180deg,transparent,#0D9488 30%,#0D9488 70%,transparent)"></div>

    {{-- Contenido --}}
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:pl-12">

        {{-- Badge ubicación --}}
        <div class="inline-flex items-center gap-2 text-[#22D3EE] text-[13px] font-bold px-3 py-1.5 rounded-lg mb-8 uppercase tracking-widest"
             style="background:rgba(13,148,136,0.10);border:1px solid rgba(13,148,136,0.30)">
            <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            Punta Arenas, Magallanes
        </div>

        {{-- Heading con palabras destacadas en gradiente --}}
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight max-w-3xl mb-6"
            style="letter-spacing:-0.03em">
            Soluciones
            <span style="background:linear-gradient(90deg,#0D9488,#22D3EE);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"> Eléctricas</span>
            para la
            <span style="background:linear-gradient(90deg,#0D9488,#22D3EE);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"> Industria</span>
        </h1>

        <p class="text-white/50 text-lg max-w-xl mb-10 leading-relaxed">
            Ingeniería de alto valor en Magallanes y Aysén
        </p>

        <div class="flex flex-wrap gap-4">
            {{-- WhatsApp CTA --}}
            <a href="https://wa.me/56983408714" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded transition-colors"
               style="background:#25D366;color:#fff"
               onmouseover="this.style.background='#1ebe5d'" onmouseout="this.style.background='#25D366'">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <a href="/#servicios"
               class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded transition-colors"
               style="border:1px solid rgba(255,255,255,0.35);color:rgba(255,255,255,0.8)"
               onmouseover="this.style.color='#fff';this.style.borderColor='rgba(255,255,255,0.55)'"
               onmouseout="this.style.color='rgba(255,255,255,0.8)';this.style.borderColor='rgba(255,255,255,0.35)'">
                Ver Servicios <span aria-hidden="true">↓</span>
            </a>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div aria-hidden="true"
         class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-50 z-20">
        <div class="w-0.5 h-8 animate-bounce"
             style="background:linear-gradient(to bottom,white,transparent)"></div>
        <span class="text-white/60 text-[11px] uppercase" style="letter-spacing:3px">Scroll</span>
    </div>
</section>

{{-- ParallaxDivider --}}
<div class="relative overflow-hidden" style="height:42vh;min-height:260px"
     x-data="parallaxDivider()" x-init="init()">
    <div x-ref="img" class="absolute will-change-transform" style="inset:-25% 0">
        <img src="/images/parallax-divider.jpg" alt="" aria-hidden="true"
             class="w-full h-full object-cover object-center">
    </div>
    <div class="absolute inset-0" style="background:rgba(4,9,26,0.45)"></div>
</div>

{{-- VRM Dashboard --}}
@livewire('vrm-dashboard')

{{-- Servicios preview --}}
@php
$iconoImagen = [
    'zap'      => '/images/srv-distribucion.jpg',
    'activity' => '/images/srv-media-tension.jpg',
    'wind'     => '/images/srv-hvac.jpg',
    'network'  => '/images/srv-networking.jpg',
    'monitor'  => '/images/srv-audio.jpg',
    'flame'    => '/images/srv-incendios.jpg',
    'settings' => '/images/srv-industrial.jpg',
    'git-merge'=> '/images/srv-distribucion.jpg',
];
@endphp
<section id="servicios" class="py-20 bg-[#0a1628]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-[#0D9488] text-[10px] font-bold uppercase tracking-widest mb-4">Nuestros Servicios</p>
        <h2 class="text-white font-black text-3xl mb-10">Soluciones a medida</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($servicios as $s)
            @php $img = $iconoImagen[$s->icono] ?? '/images/srv-tableros.jpg'; @endphp
            <a href="/servicios/{{ $s->slug }}"
               class="group relative flex flex-col overflow-hidden rounded-xl hover:scale-[1.02] transition-transform duration-300"
               style="min-height:260px">
                <img src="{{ $img }}" alt="{{ $s->titulo }}"
                     class="absolute inset-0 w-full h-full object-cover transition-all duration-500"
                     style="filter:brightness(0.5)"
                     onmouseover="this.style.filter='brightness(0.6)'" onmouseout="this.style.filter='brightness(0.5)'">
                <div aria-hidden="true" class="absolute inset-0 pointer-events-none"
                     style="background:linear-gradient(to top,rgba(5,15,35,0.96) 0%,rgba(5,15,35,0.55) 50%,transparent 100%)"></div>
                <div aria-hidden="true" class="absolute top-0 left-0 right-0 h-0.5"
                     style="background:linear-gradient(to right,#0D9488,transparent)"></div>
                <div class="relative flex flex-col flex-1 p-6 mt-auto justify-end">
                    <h3 class="font-bold text-lg text-white leading-tight mb-2">{{ $s->nombre }}</h3>
                    @if($s->descripcion)
                    <p class="text-white/60 text-sm leading-relaxed line-clamp-3">{{ Str::limit($s->descripcion, 100) }}</p>
                    @endif
                    <div class="flex items-center gap-1 text-[#0D9488] text-xs font-semibold mt-4">
                        Ver más <span aria-hidden="true" class="group-hover:translate-x-1 transition-transform inline-block">→</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        <a href="/servicios" class="inline-block mt-8 text-white/40 hover:text-white text-xs underline transition-colors">Ver todos los servicios →</a>
    </div>
</section>

{{-- Contacto --}}
<section id="contacto" class="py-20 bg-[#060e1f]">
    <div class="max-w-2xl mx-auto px-6">
        <p class="text-[#0D9488] text-[10px] font-bold uppercase tracking-widest mb-4 text-center">Contáctanos</p>
        <h2 class="text-white font-black text-3xl mb-10 text-center" style="font-family: var(--font-heading)">¿Tienes un proyecto?</h2>
        @livewire('contact-form')
    </div>
</section>
@endsection
