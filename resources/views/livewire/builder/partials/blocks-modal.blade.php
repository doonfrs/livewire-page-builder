<!-- Modal for Adding Block -->
<div class="fixed inset-0 z-52 flex items-center justify-center bg-black/40" x-data="{
    blockFilter: '',
    selectedCategory: '',
    allBlocks: @js($allBlocks),
    get categories() {
        return [...new Set(this.allBlocks.map(b => b.category).filter(c => c))].sort();
    },
    get visibleCount() {
        return this.allBlocks.filter(b => {
            const catMatch = this.selectedCategory === '' || b.category === this.selectedCategory;
            const search = this.blockFilter.toLowerCase();
            const textMatch = search === '' || b.label.toLowerCase().includes(search) || b.alias.toLowerCase().includes(search);
            return catMatch && textMatch;
        }).length;
    }
}">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl p-6 relative"
        @click.outside="$wire.closeBlockModal()">
        <button wire:click="closeBlockModal"
            class="absolute top-3 end-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <x-heroicon-o-plus class="w-6 h-6 text-pink-500" />
            {{ __('Add Block') }}
        </h2>

        <!-- Search filter -->
        <div class="relative mb-4">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            </div>
            <input type="text" x-model="blockFilter"
                x-init="$nextTick(() => $el.focus())"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-pink-500 focus:border-pink-500 block w-full ps-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-pink-500 dark:focus:border-pink-500"
                placeholder="{{ __('Search blocks...') }}">
        </div>

        <div class="flex gap-4">
            <!-- Category sidebar -->
            <div class="w-40 shrink-0 flex flex-col gap-0.5 h-[45vh] overflow-y-auto pe-1">
                <button
                    @click="selectedCategory = ''"
                    :class="selectedCategory === '' ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="text-start px-3 py-2 rounded-lg text-sm transition-colors w-full">
                    {{ __('All') }}
                </button>
                <template x-for="cat in categories" :key="cat">
                    <button
                        @click="selectedCategory = cat"
                        :class="selectedCategory === cat ? 'bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="text-start px-3 py-2 rounded-lg text-sm transition-colors w-full"
                        x-text="cat">
                    </button>
                </template>
            </div>

            <!-- Blocks grid -->
            <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-3 h-[45vh] overflow-y-auto content-start">
                @foreach ($allBlocks as $block)
                    <button
                        x-show="
                            (selectedCategory === '' || selectedCategory === $el.dataset.category) &&
                            (blockFilter === '' || $el.dataset.label.includes(blockFilter.toLowerCase()) || $el.dataset.alias.includes(blockFilter.toLowerCase()))
                        "
                        data-category="{{ $block['category'] ?? '' }}"
                        data-label="{{ strtolower($block['label']) }}"
                        data-alias="{{ strtolower($block['alias']) }}"
                        wire:click="addBlockToModalRow('{{ $block['alias'] }}', {{ isset($block['blockPageName']) ? '\'' . $block['blockPageName'] . '\'' : 'null' }})"
                        class="group h-28 border rounded-xl shadow-sm transition-all duration-200 p-4 flex flex-col items-center text-center focus:outline-none focus:ring-2 focus:ring-pink-200 relative {{ isset($block['blockPageName']) ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600 hover:shadow-md hover:bg-blue-100 dark:hover:bg-blue-900/30' : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 hover:shadow-md hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                        title="{{ isset($block['blockPageName']) ? __('This is a page component that can be reused across themes') : __('Standard content block') }}">
                        <div class="w-9 h-9 mb-2 transition-colors {{ isset($block['blockPageName']) ? 'text-blue-500 group-hover:text-blue-600' : 'text-pink-500 group-hover:text-pink-600' }}">
                            @if (isset($block['blockPageName']))
                                <x-heroicon-o-document class="w-9 h-9" />
                            @else
                                <x-dynamic-component :component="$block['icon'] ?? 'heroicon-o-cube'" class="w-9 h-9" />
                            @endif
                        </div>
                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm leading-tight">{{ $block['label'] }}</div>
                    </button>
                @endforeach

                <!-- Empty state -->
                <div x-show="visibleCount === 0" class="col-span-full text-gray-400 text-center py-8">
                    {{ __('No blocks available') }}
                </div>
            </div>
        </div>
    </div>
</div>
