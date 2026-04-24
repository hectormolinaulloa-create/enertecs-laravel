@extends('layouts.app')
@section('title', 'Enertecs — Ingeniería Eléctrica en Patagonia')
@section('content')

{{-- Hero --}}
<section class="relative h-screen flex items-center justify-center text-center px-6 overflow-hidden bg-[#0a1628]">
    <div class="relative z-10">
        <p class="text-[#0D9488] text-xs font-bold uppercase tracking-widest mb-4">Ingeniería Eléctrica · Patagonia</p>
        <h1 class="text-white font-black text-5xl md:text-7xl mb-6 leading-tight" style="font-family: var(--font-heading)">
            Soluciones<br>Eléctricas<br><span class="text-[#0067FF]">Integrales</span>
        </h1>
        <p class="text-white/60 text-lg max-w-xl mx-auto mb-8">
            Diseño, instalación y mantención de sistemas eléctricos de alta exigencia en Chile austral.
        </p>
        <div class="flex gap-4 justify-center">
            <a href="#contacto" class="bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold px-8 py-3 rounded-xl transition-colors">Contáctanos</a>
            <a href="/experiencia" class="border border-white/20 hover:border-white text-white font-bold px-8 py-3 rounded-xl transition-colors">Ver proyectos</a>
        </div>
    </div>
</section>

{{-- VRM Dashboard --}}
<section class="py-16 bg-[#060e1f]">
    <div class="max-w-7xl mx-auto px-6">
        <p class="text-white/30 text-[10px] font-bold uppercase tracking-widest mb-6">Sistema de monitoreo en vivo</p>
        @livewire('vrm-dashboard')
    </div>
</section>

{{-- Servicios preview --}}
<section class="py-20 bg-[#0a1628]">
    <div class="max-w-7xl mx-auto px-6">
        <p class="text-[#0D9488] text-[10px] font-bold uppercase tracking-widest mb-4">Nuestros Servicios</p>
        <h2 class="text-white font-black text-3xl mb-10" style="font-family: var(--font-heading)">Soluciones a medida</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($servicios as $s)
            <a href="/servicios/{{ $s->slug }}"
               class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-5 hover:border-[#0067FF]/40 transition-colors group">
                <p class="text-[#0067FF] text-xs font-bold uppercase tracking-widest mb-2 group-hover:text-[#0D9488] transition-colors">{{ $s->icono }}</p>
                <p class="text-white font-black text-sm">{{ $s->nombre }}</p>
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
