<div class="relative" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="btn bg-base-100 gap-1 px-3"
        title="{{ $availableLocales[$currentLocale] ?? $currentLocale }}">
        <x-heroicon-o-globe-alt class="w-4 h-4" />
        <span>{{ strtoupper($currentLocale) }}</span>
        <x-heroicon-o-chevron-down class="w-3 h-3" />
    </button>

    <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute end-0 z-50 mt-2 w-48 rounded-box bg-base-100 shadow-lg border border-base-300 py-1">
        @foreach ($availableLocales as $code => $name)
            <button type="button" wire:click="switchLocale('{{ $code }}')" @click="open = false"
                class="flex items-center gap-2 w-full px-4 py-2 text-start text-sm hover:bg-base-200 {{ $code === $currentLocale ? 'font-semibold' : '' }}">
                <span class="w-7 text-xs font-medium opacity-60">{{ strtoupper($code) }}</span>
                <span class="flex-1">{{ $name }}</span>
                @if ($code === $currentLocale)
                    <x-heroicon-o-check class="w-4 h-4" />
                @endif
            </button>
        @endforeach
    </div>
</div>
