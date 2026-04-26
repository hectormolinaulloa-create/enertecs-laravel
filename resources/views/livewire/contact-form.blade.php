<div class="bg-[#0d1e3a] border border-white/5 rounded-2xl p-8">
    @if($enviado)
        <div class="text-center py-8">
            <p class="text-[#0D9488] font-bold text-lg mb-2">¡Mensaje enviado!</p>
            <p class="text-white/40 text-sm">Te contactaremos a la brevedad.</p>
            <button wire:click="$set('enviado', false)" class="mt-4 text-white/40 hover:text-white text-xs underline">Enviar otro mensaje</button>
        </div>
    @else
        @if($error)
            <p class="text-red-400 text-sm mb-4">{{ $error }}</p>
        @endif
        <form wire:submit="enviar" class="space-y-4">
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Nombre *</label>
                <input type="text" wire:model.blur="nombre"
                    class="w-full bg-[#0a1628] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0D9488] focus:outline-none">
                @error('nombre') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Email *</label>
                <input type="email" wire:model.blur="email"
                    class="w-full bg-[#0a1628] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0D9488] focus:outline-none">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-white/40 text-xs uppercase tracking-widest">Mensaje *</label>
                <textarea wire:model.blur="mensaje" rows="5"
                    class="w-full bg-[#0a1628] border border-white/10 rounded-xl px-4 py-3 text-white mt-1 focus:border-[#0D9488] focus:outline-none"></textarea>
                @error('mensaje') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled"
                class="w-full bg-[#0D9488] hover:bg-[#0d8a7f] text-white font-bold py-3 rounded-xl transition-colors disabled:opacity-50">
                <span wire:loading.remove>Enviar mensaje</span>
                <span wire:loading>Enviando…</span>
            </button>
        </form>
    @endif
</div>
