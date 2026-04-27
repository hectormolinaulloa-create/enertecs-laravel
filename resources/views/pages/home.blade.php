@extends('layouts.app')
@section('title', 'Enertecs — Ingeniería Eléctrica en Patagonia')
@section('description', 'Enertecs es una empresa de ingeniería eléctrica especializada en energía solar, minería y proyectos industriales en la Patagonia chilena y austral.')

@push('scripts')
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

function revealOnScroll() {
    return {
        init() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
            const els = this.$el.querySelectorAll('[data-reveal]')
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const delay = entry.target.dataset.delay || 0
                        setTimeout(() => {
                            entry.target.style.opacity = '1'
                            entry.target.style.transform = 'translateY(0)'
                        }, delay)
                        observer.unobserve(entry.target)
                    }
                })
            }, { threshold: 0.12 })
            els.forEach(el => observer.observe(el))
        }
    }
}
</script>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex items-center overflow-hidden pt-16"
         x-data="heroSection()" x-init="init()">

    <div class="absolute inset-0 z-0 bg-[#04091A]">
        <img src="/images/tablero.jpg" alt=""
             class="absolute inset-0 w-full h-full object-cover object-center opacity-60"
             style="pointer-events:none">
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

    <div aria-hidden="true" class="absolute inset-0 z-10 pointer-events-none"
         style="background:linear-gradient(to right,rgba(4,9,26,0.93) 0%,rgba(4,9,26,0.78) 60%,rgba(4,9,26,0.45) 100%)"></div>

    <div aria-hidden="true"
         class="absolute left-0 top-[20%] bottom-[20%] w-[3px] hidden lg:block z-20"
         style="background:linear-gradient(180deg,transparent,#0D9488 30%,#0D9488 70%,transparent)"></div>

    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:pl-12">

        <div class="inline-flex items-center gap-2 text-[#22D3EE] text-[13px] font-bold px-3 py-1.5 rounded-lg mb-8 uppercase tracking-widest"
             style="background:rgba(13,148,136,0.10);border:1px solid rgba(13,148,136,0.30)">
            <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            Región de Magallanes y Aysén
        </div>

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
            <a href="https://wa.me/56983408714" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded transition-colors"
               style="background:#25D366;color:#fff"
               onmouseover="this.style.background='#1ebe5d'" onmouseout="this.style.background='#25D366'">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <a href="#servicios"
               class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded transition-colors"
               style="border:1px solid rgba(255,255,255,0.35);color:rgba(255,255,255,0.8)"
               onmouseover="this.style.color='#fff';this.style.borderColor='rgba(255,255,255,0.55)'"
               onmouseout="this.style.color='rgba(255,255,255,0.8)';this.style.borderColor='rgba(255,255,255,0.35)'">
                Ver Servicios <span aria-hidden="true">↓</span>
            </a>
        </div>
    </div>

    <div aria-hidden="true"
         class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-50 z-20">
        <div class="w-0.5 h-8 animate-bounce"
             style="background:linear-gradient(to bottom,white,transparent)"></div>
        <span class="text-white/60 text-[11px] uppercase" style="letter-spacing:3px">Scroll</span>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
     STATS BAR
══════════════════════════════════════════════════════ --}}
<div class="bg-[#04091A] border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 sm:grid-cols-4">
        @foreach([['16+','Años'],['80+','Proyectos'],['10','Servicios'],['2','Regiones']] as [$val,$label])
        <div class="py-6 text-center {{ !$loop->first ? 'border-l border-white/[0.08]' : '' }}">
            <div class="text-3xl font-black text-[#0D9488] leading-none">{{ $val }}</div>
            <div class="text-white/40 text-xs uppercase tracking-widest mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     VRM DASHBOARD
══════════════════════════════════════════════════════ --}}
@livewire('vrm-dashboard')

{{-- ═══════════════════════════════════════════════════
     SERVICIOS
══════════════════════════════════════════════════════ --}}
@php
$iconoImagen = [
    'zap'       => '/images/srv-distribucion.jpg',
    'activity'  => '/images/srv-media-tension.jpg',
    'wind'      => '/images/srv-hvac.jpg',
    'network'   => '/images/srv-networking.jpg',
    'monitor'   => '/images/srv-audio.jpg',
    'flame'     => '/images/srv-incendios.jpg',
    'settings'  => '/images/srv-industrial.jpg',
    'git-merge' => '/images/srv-distribucion.jpg',
];
@endphp
<section id="servicios" class="py-24 bg-[#0a1628]" x-data="revealOnScroll()" x-init="init()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div data-reveal style="opacity:0;transform:translateY(24px);transition:opacity 0.6s ease-out,transform 0.6s ease-out"
             class="mb-12 max-w-2xl">
            <p class="text-[#0D9488] text-xs font-semibold uppercase tracking-widest mb-3">Lo que hacemos</p>
            <h2 class="text-3xl sm:text-4xl font-black text-white mb-4">Servicios integrales para la Patagonia</h2>
            <p class="text-white/50 leading-relaxed text-sm">
                Ofrecemos soluciones integrales de ingeniería eléctrica y tecnológica para la industria,
                el sector público y proyectos de infraestructura en Magallanes y Aysén. Cada servicio
                está respaldado por más de 16 años de experiencia en las condiciones más exigentes de la Patagonia.
            </p>
        </div>

        <div data-reveal data-delay="150" style="opacity:0;transform:translateY(24px);transition:opacity 0.6s ease-out,transform 0.6s ease-out">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($servicios as $s)
                @php $img = $iconoImagen[$s->icono] ?? '/images/srv-tableros.jpg'; @endphp
                <a href="/servicios/{{ $s->slug }}"
                   class="group relative flex flex-col overflow-hidden rounded-xl hover:scale-[1.02] transition-transform duration-300"
                   style="min-height:260px">
                    <img src="{{ $img }}" alt="{{ $s->titulo }}"
                         class="absolute inset-0 w-full h-full object-cover transition-all duration-500 group-hover:brightness-[0.6]"
                         style="filter:brightness(0.5)">
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
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════
     ABOUT
══════════════════════════════════════════════════════ --}}
<section id="nosotros" class="py-24 bg-[#0a1628]" x-data="revealOnScroll()" x-init="init()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            {{-- Texto --}}
            <div data-reveal style="opacity:0;transform:translateY(24px);transition:opacity 0.6s ease-out,transform 0.6s ease-out">
                <p class="text-[#0D9488] text-xs font-semibold uppercase tracking-widest mb-3">Quiénes somos</p>
                <h2 class="text-3xl sm:text-4xl font-black text-white mb-6 leading-tight">
                    Ingeniería eléctrica con raíces en la Patagonia
                </h2>
                <p class="text-white/60 leading-relaxed mb-4">
                    Fundada en 2010, Enertecs es una empresa de ingeniería eléctrica regional comprometida
                    con entregar soluciones integrales de alto valor para la industria, el sector público
                    y la infraestructura de la Patagonia chilena.
                </p>
                <p class="text-white/60 leading-relaxed mb-8">
                    Con presencia en las regiones de Magallanes y Aysén, hemos ejecutado más de 80 proyectos
                    para clientes como el MOP, Constructora Salfa, Mina Invierno, KSAT Kongsberg,
                    Empresa Portuaria Austral, Methanex y Servicios de Salud — siempre con el mismo
                    compromiso: calidad, transparencia y resultados que perduran.
                </p>
                <a href="/nosotros"
                   class="inline-flex items-center gap-2 text-[#0D9488] font-semibold text-sm px-5 py-2.5 rounded transition-colors"
                   style="border:1px solid rgba(13,148,136,0.50)"
                   onmouseover="this.style.background='rgba(13,148,136,0.10)'" onmouseout="this.style.background='transparent'">
                    Conoce al equipo <span aria-hidden="true">→</span>
                </a>
            </div>

            {{-- Imagen con stats flotantes --}}
            <div data-reveal data-delay="200" style="opacity:0;transform:translateY(24px);transition:opacity 0.6s ease-out,transform 0.6s ease-out"
                 class="relative">
                <div class="relative rounded-lg overflow-hidden" style="aspect-ratio:4/3">
                    <img src="/images/equipo.jpg" alt="Equipo Enertecs"
                         class="w-full h-full object-cover"
                         style="filter:brightness(0.75)">
                    <div aria-hidden="true" class="absolute inset-0"
                         style="background:linear-gradient(to top,rgba(10,22,40,0.8) 0%,transparent 60%)"></div>
                </div>

                {{-- Stats flotantes --}}
                <div class="absolute grid grid-cols-3 gap-1 rounded-lg p-4"
                     style="bottom:-24px;left:-24px;background:#0d1e3a;border:1px solid rgba(13,148,136,0.20)">
                    @foreach([['16+','Años'],['80+','Proyectos'],['10','Servicios']] as [$v,$l])
                    <div class="px-4 py-2 text-center">
                        <div class="text-xl font-black text-[#0D9488] leading-none">{{ $v }}</div>
                        <div class="text-white/40 text-[10px] uppercase tracking-widest mt-0.5">{{ $l }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
     CERTIFICACIONES
══════════════════════════════════════════════════════ --}}
@php
$fallbackBrands = ['Schneider', 'DAHUA', 'BASH', 'SCHARFSTEIN', 'SELECOM', 'Victron Energy'];
@endphp
<section class="py-16 bg-[#04091A] border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-white/30 text-xs uppercase tracking-widest mb-10">Certificados por</p>
        <div class="flex flex-wrap justify-center items-center gap-10">
            @if($certificaciones->isNotEmpty())
                @foreach($certificaciones as $cert)
                <div class="opacity-40 hover:opacity-90 transition-opacity duration-300 cursor-default">
                    @if($cert->archivo)
                    <img src="/storage/{{ $cert->archivo }}" alt="{{ $cert->nombre }}"
                         style="height:40px;width:auto;object-fit:contain">
                    @else
                    <span class="text-white/60 font-bold text-sm uppercase tracking-wider">{{ $cert->nombre }}</span>
                    @endif
                </div>
                @endforeach
            @else
                @foreach($fallbackBrands as $brand)
                <div class="opacity-60 hover:opacity-100 transition-opacity duration-300 cursor-default rounded-lg px-6 py-3"
                     style="background:#0d1e3a;border:1px solid rgba(255,255,255,0.10)">
                    <span class="text-white/70 font-bold text-sm uppercase tracking-wider">{{ $brand }}</span>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════
     CONTACTO
══════════════════════════════════════════════════════ --}}
<section id="contacto" class="py-24 bg-[#060e1f]" x-data="revealOnScroll()" x-init="init()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-reveal style="opacity:0;transform:translateY(24px);transition:opacity 0.6s ease-out,transform 0.6s ease-out"
             class="grid grid-cols-1 lg:grid-cols-2 gap-16">

            {{-- Info columna izquierda --}}
            <div>
                <p class="text-[#0D9488] text-xs font-semibold uppercase tracking-widest mb-3">Hablemos</p>
                <h2 class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">Contacto</h2>
                <p class="text-white/50 mb-8 leading-relaxed">
                    La forma más rápida de contactarnos es por WhatsApp. También puedes enviarnos un mensaje
                    y te respondemos a la brevedad.
                </p>

                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-3 text-white/60">
                        <span class="text-[#0D9488]">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.64A2 2 0 012 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" />
                            </svg>
                        </span>
                        <span class="text-sm">+56 61 2 222 316 / 226 048</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/60">
                        <span class="text-[#0D9488]">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                        </span>
                        <a href="mailto:contacto@enertecs.cl" class="text-sm hover:text-white transition-colors">
                            contacto@enertecs.cl
                        </a>
                    </div>
                    <div class="flex items-start gap-3 text-white/60">
                        <span class="text-[#0D9488] mt-0.5">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </span>
                        <span class="text-sm">Punta Arenas, Región de Magallanes, Chile</span>
                    </div>
                </div>

                <a href="https://wa.me/56983408714" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded text-white transition-colors"
                   style="background:#25D366"
                   onmouseover="this.style.background='#1ebe5d'" onmouseout="this.style.background='#25D366'">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Escríbenos por WhatsApp
                </a>
            </div>

            {{-- Formulario columna derecha --}}
            @livewire('contact-form')

        </div>
    </div>
</section>

@endsection
