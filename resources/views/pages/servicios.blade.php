@extends('layouts.app')
@section('title', 'Servicios — Enertecs')
@section('content')
<section class="min-h-screen bg-[#0a1628] py-20">
  <div class="max-w-7xl mx-auto px-6">
    <h1 class="text-white font-black text-3xl mb-10" style="font-family: var(--font-heading)">Servicios</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($servicios as $s)
        <a href="/servicios/{{ $s->slug }}"
           class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-6 hover:border-[#0067FF]/40 transition-colors">
            <p class="text-white font-black mb-2">{{ $s->nombre }}</p>
            <p class="text-white/50 text-xs">{{ Str::limit($s->descripcion, 100) }}</p>
        </a>
        @endforeach
    </div>
  </div>
</section>
@endsection
