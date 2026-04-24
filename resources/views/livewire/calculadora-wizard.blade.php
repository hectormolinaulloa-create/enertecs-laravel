<div class="max-w-2xl mx-auto py-12 px-6">
    @if($error)
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-red-400 text-sm mb-6">
            {{ $error }}
            <button wire:click="$set('error', '')" class="ml-2 underline">Cerrar</button>
        </div>
    @endif

    {{-- Step 1: Upload --}}
    @if($step === 1)
        <h2 class="text-white font-black text-xl mb-6">Sube tu boleta eléctrica</h2>
        <form wire:submit="subirPdf">
            <div class="bg-[#0d1e3a] border-2 border-dashed border-white/20 rounded-2xl p-10 text-center mb-4">
                <input type="file" wire:model="pdf" accept=".pdf" class="hidden" id="pdf-input">
                <label for="pdf-input" class="cursor-pointer">
                    <p class="text-white/60 text-sm">Arrastra tu PDF aquí o <span class="text-[#0067FF] underline">selecciona archivo</span></p>
                </label>
                @if($pdf) <p class="text-[#0D9488] text-xs mt-2">{{ $pdf->getClientOriginalName() }}</p> @endif
            </div>
            <button type="submit" wire:loading.attr="disabled"
                class="w-full bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors disabled:opacity-50">
                <span wire:loading.remove>Analizar boleta con IA</span>
                <span wire:loading>Subiendo…</span>
            </button>
        </form>
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
        <h2 class="text-white font-black text-xl mb-6">Confirma tus datos</h2>
        <div class="space-y-4">
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Consumo mensual (kWh)</label>
                <input type="number" wire:model="datosBoleta.consumo_kwh"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Región</label>
                <input type="text" wire:model="datosBoleta.region"
                    class="w-full bg-[#0d1e3a] border border-white/10 rounded-xl px-4 py-3 text-white mt-1">
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
        </div>
    @endif

    {{-- Step 4: Contacto --}}
    @if($step === 4)
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
        </div>
    @endif

    {{-- Step 5: Resultado --}}
    @if($step === 5)
        <h2 class="text-white font-black text-xl mb-6">Tu sistema solar estimado</h2>
        <div class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-6 space-y-4 mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-white/40 text-xs">Potencia</p><p class="text-white font-black text-2xl">{{ number_format($resultado['potencia_real_kwp'] ?? 0, 2) }} kWp</p></div>
                <div><p class="text-white/40 text-xs">Paneles</p><p class="text-white font-black text-2xl">{{ $resultado['n_paneles'] ?? '—' }}</p></div>
                <div><p class="text-white/40 text-xs">Ahorro mensual est.</p><p class="text-green-400 font-black text-xl">${{ number_format($resultado['ahorro_mensual_clp'] ?? 0) }} CLP</p></div>
                <div><p class="text-white/40 text-xs">Retorno inversión</p><p class="text-white font-black text-xl">{{ number_format($resultado['roi_anos'] ?? 0, 1) }} años</p></div>
            </div>
        </div>
        @if($solicitudId)
            <a href="{{ route('calculadora.informe', $solicitudId) }}"
               class="w-full flex items-center justify-center gap-2 bg-[#0067FF] hover:bg-[#0050CC] text-white font-bold py-3 rounded-xl transition-colors">
                Descargar informe PDF
            </a>
        @endif
    @endif
</div>
