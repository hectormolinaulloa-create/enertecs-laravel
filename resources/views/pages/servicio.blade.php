@extends('layouts.app')
@section('title', $servicio->nombre . ' — Enertecs')

@section('content')
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
    'bolt'      => '/images/srv-distribucion.jpg',
    'industry'  => '/images/srv-tableros.jpg',
    'factory'   => '/images/srv-industrial.jpg',
    'solar'     => '/images/srv-fotovoltaico.jpg',
    'cctv'      => '/images/srv-seguridad.jpg',
    'control'   => '/images/srv-automatizacion.jpg',
    'shield'    => '/images/srv-media-tension.jpg',
    'fire'      => '/images/srv-incendios.jpg',
    'hvac'      => '/images/srv-hvac.jpg',
    'audio'     => '/images/srv-audio.jpg',
    'monitor'   => '/images/srv-montaje.jpg',
];
$imgSrc = $iconoImagen[$servicio->icono] ?? '/images/srv-tableros.jpg';
@endphp

<div class="min-h-screen bg-[#0a1628]">

    {{-- Hero con imagen del servicio --}}
    <div class="relative overflow-hidden" style="height:50vh;min-height:340px">
        <img src="{{ $imgSrc }}" alt="{{ $servicio->nombre }}"
             class="absolute inset-0 w-full h-full object-cover"
             style="filter:brightness(0.40)">
        <div aria-hidden="true" class="absolute inset-0"
             style="background:linear-gradient(to bottom,rgba(10,22,40,0.4) 0%,rgba(10,22,40,0.9) 100%)"></div>

        <div class="absolute inset-0 flex flex-col justify-end px-4 pb-10 pt-24"
             style="max-width:80rem;margin:0 auto;left:0;right:0">
            <a href="/servicios"
               class="text-white/40 hover:text-white/80 text-xs uppercase tracking-widest mb-4 inline-flex items-center gap-2 transition-colors">
                ← Servicios
            </a>
            <h1 class="text-4xl sm:text-5xl font-black text-white leading-tight max-w-2xl"
                style="letter-spacing:-0.03em">
                {{ $servicio->nombre }}
            </h1>
        </div>
    </div>

    {{-- Contenido --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

        @if($servicio->descripcion)
        <p class="text-white/60 text-lg leading-relaxed mb-8">{{ $servicio->descripcion }}</p>
        @endif

        {{-- CTA WhatsApp --}}
        <div class="mt-12 pt-8" style="border-top:1px solid rgba(255,255,255,0.05)">
            <p class="text-white/50 text-sm mb-4">
                ¿Necesitas este servicio? Contáctanos directamente.
            </p>
            <a href="https://wa.me/56983408714?text={{ urlencode('Hola, quiero consultar sobre el servicio: ' . $servicio->nombre) }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 font-semibold px-5 py-2.5 rounded text-white transition-colors"
               style="background:#25D366"
               onmouseover="this.style.background='#1ebe5d'" onmouseout="this.style.background='#25D366'">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Consultar sobre {{ $servicio->nombre }}
            </a>
        </div>
    </div>

</div>
@endsection
