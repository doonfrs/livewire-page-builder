<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
        class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium">
        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"></path>
        </svg>
        <span class="hidden sm:inline">{{ $availableLocales[$currentLocale] ?? $currentLocale }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
        </svg>
    </button>

    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 dark:divide-gray-700">
        <div class="py-1">
            @foreach ($availableLocales as $code => $name)
                <button wire:click="switchLocale('{{ $code }}')" @click="open = false"
                    class="flex items-center w-full px-4 py-2 text-left text-sm {{ $code === $currentLocale ? 'bg-pink-50 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    @if ($code === $currentLocale)
                        <svg class="w-4 h-4 mr-2 ml-1 text-pink-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                        </svg>
                    @else
                        <span class="w-4 h-4 mr-2 ml-1"></span>
                    @endif
                    {{ $name }}
                </button>
            @endforeach
        </div>
    </div>
</div>
