<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium">
        <x-heroicon-o-globe-alt class="w-5 h-5 text-pink-500" />
        <span class="hidden sm:inline">{{ $availableLocales[$currentLocale] ?? $currentLocale }}</span>
        <x-heroicon-o-chevron-down class="w-4 h-4" />
    </button>

    <div x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 dark:divide-gray-700">
        <div class="py-1">
            @foreach($availableLocales as $code => $name)
            <button wire:click="switchLocale('{{ $code }}')" @click="open = false"
                class="flex items-center w-full px-4 py-2 text-left text-sm {{ $code === $currentLocale ? 'bg-pink-50 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                @if($code === $currentLocale)
                <x-heroicon-o-check class="w-4 h-4 mr-2 text-pink-500" />
                @else
                <span class="w-4 h-4 mr-2"></span>
                @endif
                {{ $name }}
            </button>
            @endforeach
        </div>
    </div>
</div>