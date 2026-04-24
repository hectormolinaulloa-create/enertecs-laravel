<div class="min-h-screen bg-[#0a1628]">

    {{-- Barra de progreso --}}
    @php
        $stepLabels = ['Boleta', 'Datos', 'Contacto', 'Resultado'];
        // mapea pasos internos (1-5) al índice visible (0-3)
        $stepIdx = match($step) { 1, 2 => 0, 3 => 1, 4 => 2, 5 => 3, default => 0 };
    @endphp
    <div class="border-b border-white/5 bg-[#0d1e3a]">
        <div class="max-w-2xl mx-auto px-4 py-4 flex justify-between">
            @foreach($stepLabels as $i => $label)
            <div class="flex items-center gap-2 text-xs font-bold {{ $i <= $stepIdx ? 'text-[#0067FF]' : 'text-white/20' }}">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs
                    {{ $i < $stepIdx ? 'bg-[#0067FF] text-white' : ($i === $stepIdx ? 'border-2 border-[#0067FF] text-[#0067FF]' : 'border border-white/20 text-white/20') }}">
                    {{ $i < $stepIdx ? '✓' : $i + 1 }}
                </span>
                <span class="hidden sm:inline">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Error global --}}
    @if($error)
    <div class="max-w-2xl mx-auto px-4 pt-4">
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-red-400 text-sm">
            {{ $error }}
            <button wire:click="$set('error', '')" class="ml-2 underline">Cerrar</button>
        </div>
    </div>
    @endif

    {{-- Step 1: Upload --}}
    @if($step === 1)
    <div class="py-12 px-4">

        {{-- Guía introductoria --}}
        <div class="max-w-2xl mx-auto mb-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-black text-white mb-1">Descubra si la energía solar es para usted</h1>
                <p class="text-white/45 text-sm">Gratuito · Sin visitas · Resultado inmediato</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl px-4 py-3 mb-6"
                 style="background:rgba(0,103,255,0.10);border:1px solid rgba(0,103,255,0.25)">
                <span class="text-2xl">📄</span>
                <p class="text-white/80 text-sm">
                    Necesita su <span class="text-white font-bold">boleta eléctrica en PDF</span> — descárguela desde el sitio web o app de su distribuidora (CGE, Enel, Frontel, Chilquinta, Edelmag…)
                </p>
            </div>

            <div class="grid grid-cols-4 gap-2">
                @foreach([
                    ['icon' => '📤', 'label' => "Suba\nsu boleta"],
                    ['icon' => '✏️', 'label' => "Confirme\nsus datos"],
                    ['icon' => '📲', 'label' => "Ingrese\nsu contacto"],
                    ['icon' => '📊', 'label' => "Reciba\nsu informe"],
                ] as $i => $s)
                <div class="relative flex flex-col items-center text-center gap-2 bg-[#0d1e3a] border border-white/10 rounded-xl py-4 px-2">
                    @if($i < 3)
                    <span class="absolute top-1/2 -translate-y-1/2 text-white/20 text-xs z-10" style="right:-6px">›</span>
                    @endif
                    <span class="text-3xl">{{ $s['icon'] }}</span>
                    <span class="text-[#0067FF] text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"
                          style="background:rgba(0,103,255,0.15)">{{ $i + 1 }}</span>
                    <p class="text-white/70 text-xs leading-tight" style="white-space:pre-line">{{ $s['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="max-w-2xl mx-auto border-t border-white/10 mb-8"></div>

        {{-- Upload widget --}}
        <div class="w-full max-w-lg mx-auto">
            <h2 class="text-2xl font-black text-white mb-2 text-center">Sube tu boleta eléctrica</h2>
            <p class="text-white/50 text-sm text-center mb-8">La IA extraerá automáticamente todos los datos necesarios</p>

            <form wire:submit="subirPdf" x-data="{ dragging: false }">
                <div
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave="dragging = false"
                    x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                    x-on:click="$refs.fileInput.click()"
                    class="border-2 border-dashed rounded-2xl p-12 text-center cursor-pointer transition-all"
                    :class="dragging ? 'border-[#0067FF] bg-[#0067FF]/10' : 'border-white/20 hover:border-white/40'">

                    <input x-ref="fileInput" type="file" wire:model="pdf" accept=".pdf" class="hidden"
                           id="pdf-input">

                    <div wire:loading wire:target="subirPdf" class="space-y-3">
                        <div class="w-8 h-8 border-2 border-[#0067FF] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-white/60 text-sm">Analizando boleta con IA…</p>
                    </div>
                    <div wire:loading.remove wire:target="subirPdf" class="space-y-3">
                        <div class="text-4xl">📄</div>
                        <p class="text-white font-bold">
                            @if($pdf) {{ $pdf->getClientOriginalName() }}
                            @else Arrastra tu boleta PDF aquí @endif
                        </p>
                        <p class="text-white/40 text-sm">{{ $pdf ? 'Listo para analizar' : 'o haz clic para seleccionar' }}</p>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="subirPdf"
                    class="w-full mt-4 bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="subirPdf">Analizar boleta con IA</span>
                    <span wire:loading wire:target="subirPdf">Analizando…</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Step 2: Procesando --}}
    @if($step === 2)
    <div class="text-center py-20" wire:poll.2000ms="checkJobStatus">
        <div class="w-12 h-12 border-2 border-[#0067FF] border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-white font-bold">Analizando tu boleta…</p>
        <p class="text-white/40 text-sm mt-2">La inteligencia artificial está extrayendo tus datos.</p>
    </div>
    @endif

    {{-- Step 3: Confirmar datos --}}
    @if($step === 3)
    <div class="max-w-2xl mx-auto py-12 px-6">
        <h2 class="text-white font-black text-xl mb-6">Confirma tus datos</h2>
        <div class="space-y-4">
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Consumo mensual (kWh)</label>
                <input type="number" wire:model="datosBoleta.consumo_kwh"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Región</label>
                <select wire:model="datosBoleta.region"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
                    <option value="">Selecciona tu región</option>
                    <option>Arica y Parinacota</option><option>Tarapacá</option><option>Antofagasta</option>
                    <option>Atacama</option><option>Coquimbo</option><option>Valparaíso</option>
                    <option>Metropolitana de Santiago</option><option>O'Higgins</option><option>Maule</option>
                    <option>Ñuble</option><option>Biobío</option><option>La Araucanía</option>
                    <option>Los Ríos</option><option>Los Lagos</option><option>Aysén</option><option>Magallanes</option>
                </select>
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Distribuidora</label>
                <input type="text" wire:model="datosBoleta.distribuidora"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <button wire:click="confirmarDatos"
                class="w-full bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors">
                Calcular sistema solar
            </button>
            <button wire:click="$set('step', 1)" class="w-full text-white/40 text-sm hover:text-white/60 transition-colors">
                ← Volver
            </button>
        </div>
    </div>
    @endif

    {{-- Step 4: Contacto --}}
    @if($step === 4)
    <div class="max-w-2xl mx-auto py-12 px-6">
        <h2 class="text-white font-black text-xl mb-6">Tus datos de contacto</h2>
        <div class="space-y-4">
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Nombre *</label>
                <input type="text" wire:model="nombre"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Teléfono *</label>
                <input type="tel" wire:model="telefono"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Email</label>
                <input type="email" wire:model="email"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Empresa (opcional)</label>
                <input type="text" wire:model="empresa"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <button wire:click="guardarContacto"
                class="w-full bg-[#0D9488] hover:bg-[#0d8a7f] text-white font-bold py-3 rounded-xl transition-colors">
                Ver mi resultado
            </button>
            <button wire:click="$set('step', 3)" class="w-full text-white/40 text-sm hover:text-white/60 transition-colors">
                ← Volver
            </button>
        </div>
    </div>
    @endif

    {{-- Step 5: Resultado --}}
    @if($step === 5)
    <div class="max-w-2xl mx-auto py-12 px-6">
        <h2 class="text-white font-black text-xl mb-6">Tu sistema solar estimado</h2>
        <div class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-6 space-y-4 mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-white/40 text-xs">Potencia</p>
                    <p class="text-white font-black text-2xl">{{ number_format($resultado['potencia_real_kwp'] ?? 0, 2) }} kWp</p>
                </div>
                <div>
                    <p class="text-white/40 text-xs">Paneles</p>
                    <p class="text-white font-black text-2xl">{{ $resultado['n_paneles'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-white/40 text-xs">Ahorro mensual est.</p>
                    <p class="text-green-400 font-black text-xl">${{ number_format($resultado['ahorro_mensual_clp'] ?? 0) }} CLP</p>
                </div>
                <div>
                    <p class="text-white/40 text-xs">Retorno inversión</p>
                    <p class="text-white font-black text-xl">{{ number_format($resultado['roi_anos'] ?? 0, 1) }} años</p>
                </div>
            </div>
        </div>
        @if($solicitudUuid)
        <a href="{{ route('calculadora.informe', $solicitudUuid) }}"
           class="w-full flex items-center justify-center gap-2 bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors">
            Descargar informe PDF
        </a>
        @endif
        <button wire:click="reiniciar" class="w-full mt-3 text-white/40 text-sm hover:text-white/60 transition-colors">
            Nueva consulta
        </button>
    </div>
    @endif

</div>
