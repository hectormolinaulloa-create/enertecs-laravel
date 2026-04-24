@extends('layouts.app')
@section('title', 'Calculadora Solar OnGrid — Enertecs')
@section('content')
    <section class="min-h-screen bg-[#0a1628] py-12">
        <div class="max-w-7xl mx-auto px-6">
            <h1 class="text-white font-black text-3xl mb-2" style="font-family: var(--font-heading)">
                Calculadora Solar OnGrid
            </h1>
            <p class="text-white/40 text-sm mb-10">Sube tu boleta y calcula tu sistema fotovoltaico en segundos.</p>
            @livewire('calculadora-wizard')
        </div>
    </section>
@endsection
