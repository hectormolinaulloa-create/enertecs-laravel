<div @class(['min-h-screen', 'bg-[#0a1628]' => $step !== 4, 'bg-[#f0f4f8]' => $step === 4])>

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
    @php
        $nombre        = $datosBoleta['nombre_cliente'] ?? 'Cliente';
        $distribuidora = $datosBoleta['distribuidora'] ?? 'su distribuidora';
        $ahorro        = $resultado['ahorro_mensual_clp'] ?? 0;
        $roi           = $resultado['roi_anos'] ?? 0;
        $pctReduccion  = $resultado['porcentaje_reduccion'] ?? 0;
        $costoSin      = $resultado['costo_sin_solar_clp'] ?? 0;
        $costoConSolar = $resultado['costo_con_solar_clp'] ?? 0;
        $barraSolar    = $costoSin > 0 ? max(4, (int) round($costoConSolar / $costoSin * 64)) : 4;
        $nPaneles      = $resultado['n_paneles'] ?? 0;
        $panel         = $resultado['panel'] ?? [];
        $inversor      = $resultado['inversor'] ?? [];
        $nInversores   = $resultado['n_inversores'] ?? 1;
        $kwp           = $resultado['potencia_real_kwp'] ?? 0;
        $areaM2        = $resultado['area_m2'] ?? 0;
        $co2           = $resultado['co2_kg_anual'] ?? 0;
        $waMsg         = rawurlencode("Hola Felipe, soy {$nombre} y acabo de ver mi análisis solar en Enertecs. Me gustaría conocer más sobre instalar paneles en mi hogar.");
        $waUrl         = "https://wa.me/56935165830?text={$waMsg}";
    @endphp
    <div class="max-w-md mx-auto pb-10">

        {{-- Encabezado azul --}}
        <div class="bg-[#0067FF] px-6 pt-5 pb-8">
            <div class="text-white/65 text-[11px] mb-1.5">Enertecs SpA · Análisis solar con IA</div>
            <h1 class="text-white text-xl font-black leading-tight mb-1">Buenas noticias, {{ $nombre }}.</h1>
            <p class="text-white/75 text-xs">Preparamos su estimación solar en base a su boleta de {{ $distribuidora }}.</p>
        </div>

        {{-- Hero KPI flotante --}}
        <div class="mx-4 -mt-3 bg-white rounded-2xl p-5 shadow-xl">
            <p class="text-slate-500 text-[11px] mb-1">Si instala un sistema solar hoy, usted podría ahorrar</p>
            <div class="text-4xl font-black text-[#0067FF] leading-none">${{ number_format($ahorro, 0, ',', '.') }}</div>
            <p class="text-slate-500 text-[11px] mt-1">al mes — <strong class="text-green-600">desde el primer día de operación</strong></p>
        </div>

        <div class="px-4 pt-4 space-y-4">

            {{-- 3 KPIs --}}
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-white rounded-xl p-3 shadow-sm">
                    <div class="text-lg font-black text-[#0067FF]">${{ number_format($ahorro, 0, ',', '.') }}</div>
                    <div class="text-[9px] font-bold text-gray-700 mt-0.5">Ahorro mensual</div>
                    <div class="text-[8px] text-gray-400 leading-tight mt-0.5">Lo que deja de pagarle a la distribuidora cada mes</div>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm">
                    <div class="text-lg font-black text-emerald-600">{{ number_format($roi, 1) }} a.</div>
                    <div class="text-[9px] font-bold text-gray-700 mt-0.5">Recupera su inversión</div>
                    <div class="text-[8px] text-gray-400 leading-tight mt-0.5">Después de ese plazo, la energía solar es sin costo</div>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm">
                    <div class="text-lg font-black text-violet-600">−{{ $pctReduccion }}%</div>
                    <div class="text-[9px] font-bold text-gray-700 mt-0.5">Baja su boleta</div>
                    <div class="text-[8px] text-gray-400 leading-tight mt-0.5">De ${{ number_format($costoSin, 0, ',', '.') }} a ~${{ number_format($costoConSolar, 0, ',', '.') }} al mes</div>
                </div>
            </div>

            {{-- Simulación de boleta --}}
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-[11px] font-bold text-gray-700 mb-3">Así se vería su boleta</p>
                <div class="flex items-end gap-4">
                    <div class="flex-1 text-center">
                        <div class="text-[10px] text-gray-500 mb-1">Hoy</div>
                        <div class="bg-red-100 rounded-t-lg h-16 flex items-center justify-center">
                            <span class="text-red-600 text-[13px] font-black">${{ number_format($costoSin, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-red-300 h-0.5 rounded-b"></div>
                    </div>
                    <div class="text-gray-300 text-xl pb-5">→</div>
                    <div class="flex-1 text-center">
                        <div class="text-[10px] text-gray-500 mb-1">Con solar</div>
                        <div class="bg-green-100 rounded-t-lg" style="height: {{ $barraSolar }}px;"></div>
                        <div class="bg-green-300 h-0.5 rounded-b"></div>
                        <div class="text-[13px] font-black text-green-600 mt-1.5">~${{ number_format($costoConSolar, 0, ',', '.') }}</div>
                    </div>
                </div>
                <p class="text-[8px] text-gray-400 text-center mt-2.5">Estimación basada en su consumo histórico y el precio real de su tarifa</p>
            </div>

            {{-- Su sistema solar --}}
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-[11px] font-bold text-gray-700 mb-2.5">Su sistema solar</p>
                <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-[9px]">
                    <span class="text-gray-400">Paneles</span>
                    <span class="text-gray-700 font-semibold">{{ $nPaneles }} × {{ ($panel['marca'] ?? '') }} {{ ($panel['modelo'] ?? '') }} {{ ($panel['potencia_wp'] ?? '') }} Wp</span>
                    <span class="text-gray-400">Inversor</span>
                    <span class="text-gray-700 font-semibold">@if($nInversores > 1){{ $nInversores }} × @endif{{ $inversor['modelo'] ?? '—' }}</span>
                    <span class="text-gray-400">Potencia total</span>
                    <span class="text-gray-700 font-semibold">{{ number_format($kwp, 2) }} kWp</span>
                    <span class="text-gray-400">Área de techo</span>
                    <span class="text-gray-700 font-semibold">~{{ number_format($areaM2, 1) }} m²</span>
                    <span class="text-gray-400">CO₂ que evita</span>
                    <span class="text-green-600 font-semibold">{{ number_format($co2) }} kg al año</span>
                </div>
            </div>

            {{-- Teaser PDF --}}
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-[#0067FF]">
                <p class="text-[11px] font-bold text-gray-700 mb-1">Su informe completo incluye además</p>
                <p class="text-[9px] text-slate-500 mb-2.5">Descargue el PDF para ver el detalle técnico y constructivo de su proyecto.</p>
                <ul class="text-[9px] text-slate-600 leading-loose list-disc pl-3.5">
                    <li>Dimensiones físicas del inversor (para la instalación)</li>
                    <li>Metros cuadrados exactos que necesita en su techo</li>
                    <li>Configuración eléctrica: strings y cableado</li>
                    <li>Proyección de ahorro a 25 años</li>
                    <li>Ficha técnica completa del panel solar</li>
                    <li>Datos de contacto de Enertecs SpA</li>
                </ul>
                @if($solicitudUuid)
                <a href="{{ route('calculadora.informe', $solicitudUuid) }}"
                   class="mt-3 flex items-center justify-center gap-2 bg-[#0067FF] hover:bg-[#0050CC] text-white text-[10px] font-bold py-2.5 rounded-xl transition-colors">
                    Descargar informe PDF
                </a>
                @endif
            </div>

            {{-- CTA Felipe Araya --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                <p class="text-sm font-bold text-blue-800 mb-0.5">¿Le interesa avanzar?</p>
                <p class="text-[10px] text-blue-500 mb-3">Nuestro ingeniero lo asesora sin compromiso.</p>
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-10 h-10 bg-[#0067FF] rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0">FA</div>
                    <div>
                        <div class="text-sm font-bold text-slate-800">Felipe Araya</div>
                        <div class="text-[10px] text-slate-500">Ingeniero de Desarrollo de Negocio</div>
                    </div>
                </div>
                <a href="{{ $waUrl }}"
                   target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1fbe5a] text-white text-[11px] font-bold py-3 rounded-xl transition-colors">
                    Escribirle por WhatsApp
                </a>
                <p class="text-[9px] text-slate-400 text-center mt-1.5">+56 9 3516 5830</p>
            </div>

            {{-- Nueva consulta --}}
            <button wire:click="reiniciar"
                    class="w-full bg-white border border-slate-200 rounded-xl py-3 text-slate-400 text-xs hover:text-slate-600 transition-colors">
                Nueva consulta
            </button>

        </div>
    </div>
    @endif

</div>
