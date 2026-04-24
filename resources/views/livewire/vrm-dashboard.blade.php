<div wire:poll.60000ms="fetchData" class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-6">
    @if($error)
        <p class="text-white/40 text-xs text-center py-8">{{ $errorMsg }}</p>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- PV Power --}}
            <div class="text-center">
                <p class="text-white/30 text-[10px] uppercase tracking-widest mb-1">Solar</p>
                <p class="text-white font-black text-2xl">{{ number_format(($stats['pv_power'] ?? 0) / 1000, 1) }}</p>
                <p class="text-white/40 text-xs">kW</p>
                <div x-data="{ val: {{ min(100, (($stats['pv_power'] ?? 0) / 5000) * 100) }} }" class="mt-2">
                    <svg viewBox="0 0 100 4" class="w-full">
                        <rect width="100" height="4" rx="2" fill="rgba(255,255,255,0.05)"/>
                        <rect :width="val" height="4" rx="2" fill="#F59E0B"/>
                    </svg>
                </div>
            </div>
            {{-- Consumo --}}
            <div class="text-center">
                <p class="text-white/30 text-[10px] uppercase tracking-widest mb-1">Consumo</p>
                <p class="text-white font-black text-2xl">{{ number_format(($stats['consumption'] ?? 0) / 1000, 1) }}</p>
                <p class="text-white/40 text-xs">kW</p>
                <div x-data="{ val: {{ min(100, (($stats['consumption'] ?? 0) / 5000) * 100) }} }" class="mt-2">
                    <svg viewBox="0 0 100 4" class="w-full">
                        <rect width="100" height="4" rx="2" fill="rgba(255,255,255,0.05)"/>
                        <rect :width="val" height="4" rx="2" fill="#0067FF"/>
                    </svg>
                </div>
            </div>
            {{-- Batería --}}
            <div class="text-center">
                <p class="text-white/30 text-[10px] uppercase tracking-widest mb-1">Batería</p>
                <p class="text-white font-black text-2xl">{{ $stats['battery_soc'] ?? 0 }}</p>
                <p class="text-white/40 text-xs">%</p>
                <div x-data="{ val: {{ $stats['battery_soc'] ?? 0 }} }" class="mt-2">
                    <svg viewBox="0 0 100 4" class="w-full">
                        <rect width="100" height="4" rx="2" fill="rgba(255,255,255,0.05)"/>
                        <rect :width="val" height="4" rx="2" fill="#0D9488"/>
                    </svg>
                </div>
            </div>
            {{-- Red --}}
            <div class="text-center">
                <p class="text-white/30 text-[10px] uppercase tracking-widest mb-1">Red</p>
                <p class="text-white font-black text-2xl">{{ number_format(abs($stats['grid_power'] ?? 0) / 1000, 1) }}</p>
                <p class="text-white/40 text-xs">{{ ($stats['grid_power'] ?? 0) >= 0 ? 'kW importado' : 'kW exportado' }}</p>
            </div>
        </div>
    @endif
</div>
