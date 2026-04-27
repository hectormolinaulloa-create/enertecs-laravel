<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enertecs — Ingeniería Eléctrica')</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="bg-[#0a1628] text-white antialiased">

    {{-- Navbar --}}
    <header class="fixed top-0 left-0 right-0 z-40 border-b border-white/5 transition-all duration-300"
            style="background:rgba(10,22,40,0.90);backdrop-filter:blur(12px)"
            x-data="{ open: false }">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center">
                <img src="/logo.png" alt="Enertecs" width="130" height="30" style="object-fit:contain">
            </a>

            {{-- Links desktop --}}
            <div class="hidden sm:flex items-center gap-6 text-sm font-medium text-white/70">
                <a href="/#servicios" class="hover:text-white transition-colors border-b-2 border-transparent hover:border-[#0D9488] pb-0.5">Servicios</a>
                <a href="/experiencia" class="hover:text-white transition-colors border-b-2 border-transparent hover:border-[#0D9488] pb-0.5">Experiencia</a>
                <a href="/nosotros" class="hover:text-white transition-colors border-b-2 border-transparent hover:border-[#0D9488] pb-0.5">Nosotros</a>
                <a href="/calculadora/solar-ongrid" class="hover:text-white transition-colors border-b-2 border-transparent hover:border-[#0D9488] pb-0.5">Calculadora</a>
                <a href="/#contacto" class="hover:text-white transition-colors border-b-2 border-transparent hover:border-[#0D9488] pb-0.5">Contacto</a>
            </div>

            {{-- Hamburger (solo mobile) --}}
            <button type="button" @click="open = !open"
                    class="sm:hidden text-white/70 hover:text-white transition-colors p-2 rounded hover:bg-white/5"
                    :aria-label="open ? 'Cerrar menú' : 'Abrir menú'">
                <svg x-show="!open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
                <svg x-show="open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </nav>

        {{-- Drawer mobile --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="sm:hidden border-t border-white/5"
             style="background:rgba(10,22,40,0.97)">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col gap-4">
                <a href="/#servicios"              @click="open=false" class="text-white/70 hover:text-white text-sm font-medium transition-colors">Servicios</a>
                <a href="/experiencia"             @click="open=false" class="text-white/70 hover:text-white text-sm font-medium transition-colors">Experiencia</a>
                <a href="/nosotros"                @click="open=false" class="text-white/70 hover:text-white text-sm font-medium transition-colors">Nosotros</a>
                <a href="/calculadora/solar-ongrid" @click="open=false" class="text-white/70 hover:text-white text-sm font-medium transition-colors">Calculadora</a>
                <a href="/#contacto"               @click="open=false" class="text-white/70 hover:text-white text-sm font-medium transition-colors">Contacto</a>
            </div>
        </div>
    </header>

    <main class="pt-16">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-[#060e1f] border-t border-white/5 pt-14 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
                {{-- Col 1: Logo + descripción --}}
                <div>
                    <a href="/" class="inline-flex items-center mb-5">
                        <img src="/logo.png" alt="Enertecs" width="120" height="28" style="object-fit:contain">
                    </a>
                    <p class="text-white/60 text-sm leading-relaxed max-w-xs">
                        Ingeniería eléctrica integral de alto valor en las regiones de Magallanes y Aysén.
                    </p>
                    <div class="mt-6 w-10 h-0.5 bg-[#0D9488]" aria-hidden="true"></div>
                </div>

                {{-- Col 2: Navegación --}}
                <div>
                    <h4 class="text-white/70 font-semibold text-xs mb-4 uppercase tracking-widest">Navegación</h4>
                    <ul class="space-y-2.5">
                        <li><a href="/servicios" class="text-white/60 hover:text-white text-sm transition-colors">Servicios</a></li>
                        <li><a href="/experiencia" class="text-white/60 hover:text-white text-sm transition-colors">Experiencia</a></li>
                        <li><a href="/nosotros" class="text-white/60 hover:text-white text-sm transition-colors">Nosotros</a></li>
                        <li><a href="/#contacto" class="text-white/60 hover:text-white text-sm transition-colors">Contacto</a></li>
                    </ul>
                </div>

                {{-- Col 3: Contacto --}}
                <div>
                    <h4 class="text-white/70 font-semibold text-xs mb-4 uppercase tracking-widest">Contacto</h4>
                    <ul class="space-y-2.5">
                        <li><a href="mailto:contacto@enertecs.cl" class="text-white/60 hover:text-white text-sm transition-colors">contacto@enertecs.cl</a></li>
                        <li><a href="tel:+56612222316" class="text-white/60 hover:text-white text-sm transition-colors">+56 61 2 222 316 / 226 048</a></li>
                        <li class="text-white/60 text-sm">Punta Arenas, Magallanes, Chile</li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-white/50 text-xs">© {{ date('Y') }} Enertecs. Todos los derechos reservados.</p>
                <p class="text-white/35 text-xs">Ingeniería Eléctrica · Magallanes · Aysén</p>
            </div>
        </div>
    </footer>

    {{-- WhatsApp flotante (solo en páginas públicas) --}}
    <a href="https://wa.me/56983408714" target="_blank" rel="noopener"
       aria-label="Contactar por WhatsApp"
       class="fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-400 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-xl transition-colors">
        <svg class="w-7 h-7" aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>

    @livewireScripts
    @stack('scripts')
</body>
</html>
