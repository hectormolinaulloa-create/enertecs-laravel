@extends('layouts.app')
@section('title', $servicio->nombre . ' — Enertecs')
@section('content')
<section class="min-h-screen bg-[#0a1628] py-20">
  <div class="max-w-4xl mx-auto px-6">
    <a href="/servicios" class="text-white/40 hover:text-white text-xs underline mb-6 inline-block">← Todos los servicios</a>
    <h1 class="text-white font-black text-3xl mb-4" style="font-family: var(--font-heading)">{{ $servicio->nombre }}</h1>
    <p class="text-white/60 text-sm leading-relaxed">{{ $servicio->descripcion }}</p>
    <div class="mt-10">
        <a href="/#contacto" class="bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold px-8 py-3 rounded-xl transition-colors">
            Solicitar cotización
        </a>
    </div>
  </div>
</section>
@endsection
