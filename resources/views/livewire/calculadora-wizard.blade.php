<div class="min-h-screen bg-[#0a1628]">

    {{-- Barra de progreso --}}
    @php
        $stepLabels = ['Boleta', 'Datos', 'Resultado'];
        $stepIdx = match($step) { 1, 2 => 0, 3 => 1, 4 => 2, default => 0 };
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

            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    ['icon' => '📤', 'label' => "Suba\nsu boleta"],
                    ['icon' => '✏️', 'label' => "Confirme\nsus datos"],
                    ['icon' => '📊', 'label' => "Reciba\nsu informe"],
                ] as $i => $s)
                <div class="relative flex flex-col items-center text-center gap-2 bg-[#0d1e3a] border border-white/10 rounded-xl py-4 px-2">
                    @if($i < 2)
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
        <div class="w-full max-w-lg mx-auto"
             x-data="{ dragging: false }"
             x-on:livewire-upload-finish="$wire.subirPdf()">

            <h2 class="text-2xl font-black text-white mb-2 text-center">Sube tu boleta eléctrica</h2>
            <p class="text-white/50 text-sm text-center mb-8">La IA extraerá automáticamente todos los datos necesarios</p>

            <div
                x-on:dragover.prevent="dragging = true"
                x-on:dragleave="dragging = false"
                x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                x-on:click="$refs.fileInput.click()"
                class="border-2 border-dashed rounded-2xl p-12 text-center cursor-pointer transition-all"
                :class="dragging ? 'border-[#0067FF] bg-[#0067FF]/10' : 'border-white/20 hover:border-white/40'">

                <input x-ref="fileInput" type="file" wire:model="pdf" accept=".pdf" class="hidden" id="pdf-input">

                {{-- Subiendo archivo al servidor --}}
                <div wire:loading wire:target="pdf" class="space-y-3">
                    <div class="w-8 h-8 border-2 border-white/40 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-white/60 text-sm">Subiendo archivo…</p>
                </div>
                {{-- Iniciando análisis --}}
                <div wire:loading wire:target="subirPdf" class="space-y-3">
                    <div class="w-8 h-8 border-2 border-[#0067FF] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-white/60 text-sm">Iniciando análisis con IA…</p>
                </div>
                {{-- Estado en reposo --}}
                <div wire:loading.remove wire:target="pdf"
                     wire:loading.remove wire:target="subirPdf"
                     class="space-y-3">
                    <div class="text-4xl">📄</div>
                    <p class="text-white font-bold">Arrastra tu boleta PDF aquí</p>
                    <p class="text-white/40 text-sm">o haz clic para seleccionar</p>
                </div>
            </div>
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

    {{-- Step 3: Confirma datos del cliente --}}
    @if($step === 3)
    <div class="max-w-2xl mx-auto py-12 px-6">
        <h2 class="text-white font-black text-xl mb-2">Confirma tus datos</h2>
        <p class="text-white/40 text-sm mb-6">Hemos extraído estos datos de tu boleta. Revisa y completa lo que falte.</p>

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-4">
            <ul class="text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="space-y-4">

            {{-- Nombre --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Nombre completo *</label>
                <input type="text" wire:model="datosBoleta.nombre_cliente"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
            </div>

            {{-- RUT --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">RUT</label>
                <input type="text" wire:model="datosBoleta.rut"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
            </div>

            {{-- Dirección y comuna en fila --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Dirección</label>
                    <input type="text" wire:model="datosBoleta.direccion"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
                </div>
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Comuna</label>
                    <input type="text" wire:model="datosBoleta.comuna"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
                </div>
            </div>

            {{-- Región --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Región *</label>
                <select wire:model="datosBoleta.region"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
                    <option value="">Selecciona tu región</option>
                    <option>Arica y Parinacota</option>
                    <option>Tarapacá</option>
                    <option>Antofagasta</option>
                    <option>Atacama</option>
                    <option>Coquimbo</option>
                    <option>Valparaíso</option>
                    <option>Metropolitana de Santiago</option>
                    <option>O'Higgins</option>
                    <option>Maule</option>
                    <option>Ñuble</option>
                    <option>Biobío</option>
                    <option>La Araucanía</option>
                    <option>Los Ríos</option>
                    <option>Los Lagos</option>
                    <option>Aysén</option>
                    <option>Magallanes</option>
                </select>
            </div>

            {{-- Teléfono y email en fila --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Teléfono *</label>
                    <input type="tel" wire:model="datosBoleta.telefono"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none"
                        placeholder="+56 9 XXXX XXXX">
                </div>
                <div>
                    <label class="text-white/40 text-xs uppercase tracking-widest">Email *</label>
                    <input type="email" wire:model="datosBoleta.email"
                        class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none"
                        placeholder="correo@ejemplo.cl">
                </div>
            </div>

            {{-- Empresa --}}
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Empresa (opcional)</label>
                <input type="text" wire:model="datosBoleta.empresa"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0067FF] outline-none">
            </div>

            {{-- Consentimiento --}}
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" wire:model="consentimiento"
                    class="mt-0.5 w-4 h-4 accent-[#0067FF] shrink-0">
                <span class="text-white/60 text-xs leading-relaxed">
                    Autorizo a Enertecs SpA a utilizar mis datos de contacto para hacerme llegar información sobre mi proyecto solar. *
                </span>
            </label>
            @error('consentimiento')
                <p class="text-red-400 text-xs">Debe aceptar el uso de sus datos para continuar.</p>
            @enderror

            <button wire:click="confirmarDatos" wire:loading.attr="disabled"
                class="w-full bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="confirmarDatos">Ver mi resultado solar</span>
                <span wire:loading wire:target="confirmarDatos">Calculando…</span>
            </button>

            <button wire:click="$set('step', 1)" class="w-full text-white/40 text-sm hover:text-white/60 transition-colors">
                ← Volver
            </button>
        </div>
    </div>
    @endif

    {{-- Step 4: Resultado --}}
    @if($step === 4)
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
