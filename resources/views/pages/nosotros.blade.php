@extends('layouts.app')
@section('title', 'Nosotros — Enertecs')

@section('content')
<div class="min-h-screen bg-[#0a1628]">

    {{-- Hero con foto de oficina --}}
    <div class="relative overflow-hidden" style="height:55vh;min-height:380px">
        <img src="/images/oficina.jpg" alt="Oficina Enertecs"
             class="absolute inset-0 w-full h-full object-cover"
             style="filter:brightness(0.5)">
        <div aria-hidden="true" class="absolute inset-0"
             style="background:linear-gradient(to bottom,rgba(10,22,40,0.5) 0%,rgba(10,22,40,0.85) 100%)"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
            <div class="text-[#0D9488] text-xs font-semibold uppercase tracking-widest mb-3">
                Quiénes somos
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight"
                style="letter-spacing:-0.03em">
                Quiénes Somos
            </h1>
        </div>
    </div>

    {{-- Contenido principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">

        {{-- Misión e historia --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start mb-20">
            <div>
                <div class="text-[#0D9488] text-xs font-semibold uppercase tracking-widest mb-3">Misión</div>
                <h2 class="text-2xl sm:text-3xl font-black text-white mb-6 leading-tight">
                    Ingeniería eléctrica comprometida con la Patagonia
                </h2>
                <div class="space-y-4 text-white/60 leading-relaxed text-sm">
                    <p>Fundada en 2010, Enertecs nació con la convicción de que el sur de Chile merecía
                    una empresa de ingeniería eléctrica local, comprometida con la calidad y con el
                    desarrollo de la región. Hoy somos un referente en Magallanes y Aysén, con más de
                    80 proyectos ejecutados en los sectores más exigentes de la Patagonia.</p>
                    <p>Trabajamos para el Ministerio de Obras Públicas, Servicios de Salud, empresas
                    mineras, operadores portuarios, constructoras y clientes privados — siempre con
                    el mismo estándar: soluciones técnicas sólidas, ejecución responsable y relaciones
                    de largo plazo basadas en la confianza.</p>
                    <p>Nuestro equipo está certificado por marcas líderes como Schneider Electric, DAHUA,
                    BASH, SCHARFSTEIN, SELECOM y Victron Energy, lo que nos permite garantizar la
                    calidad de los materiales y el cumplimiento de los estándares internacionales en
                    cada proyecto que emprendemos.</p>
                </div>
            </div>

            {{-- Foto misión --}}
            <div class="relative rounded-lg overflow-hidden" style="aspect-ratio:4/3">
                <img src="/images/mision.jpg" alt="Misión Enertecs"
                     class="absolute inset-0 w-full h-full object-cover"
                     style="filter:brightness(0.8)">
                <div aria-hidden="true" class="absolute inset-0"
                     style="background:linear-gradient(to top,rgba(10,22,40,0.7) 0%,transparent 60%)"></div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-20 max-w-2xl mx-auto w-full">
            @foreach([
                ['value' => '16+', 'label' => 'Años de experiencia'],
                ['value' => '80+', 'label' => 'Proyectos completados'],
                ['value' => '10',  'label' => 'Servicios especializados'],
            ] as $stat)
            <div class="bg-[#0d1e3a] border rounded-lg p-6 text-center"
                 style="border-color:rgba(13,148,136,0.20)">
                <div class="text-3xl font-black text-[#0D9488] leading-none mb-2">{{ $stat['value'] }}</div>
                <div class="text-white/40 text-xs uppercase tracking-widest">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Equipo --}}
        <div>
            <div class="text-[#0D9488] text-xs font-semibold uppercase tracking-widest mb-3">
                Nuestro equipo
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white mb-8 leading-tight">
                Profesionales comprometidos con la excelencia
            </h2>
            <div class="relative rounded-lg overflow-hidden" style="aspect-ratio:16/7">
                <img src="/images/equipo.jpg" alt="Equipo de trabajo Enertecs"
                     class="absolute inset-0 w-full h-full object-cover"
                     style="filter:brightness(0.75)">
                <div aria-hidden="true" class="absolute inset-0"
                     style="background:linear-gradient(to top,rgba(10,22,40,0.8) 0%,transparent 60%)"></div>
                <div class="absolute bottom-6 left-8">
                    <p class="text-white/70 text-sm font-medium">
                        Equipo Enertecs — Punta Arenas, Magallanes
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
