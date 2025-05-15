<!-- Modal for Adding Block -->
<div class="fixed inset-0 z-52 flex items-center justify-center bg-black/40"
    x-data="{ 
        blockFilter: '',
        allBlocks: @js($allBlocks),
        filteredBlocks: function() {
            if (!this.blockFilter.trim()) {
                return this.allBlocks;
            }
            
            const searchTerm = this.blockFilter.toLowerCase();
            return this.allBlocks.filter(block => {
                return block.label.toLowerCase().includes(searchTerm) || 
                       (block.alias && block.alias.toLowerCase().includes(searchTerm));
            });
        }
    }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative"
        @click.outside="$wire.closeBlockModal()">
        <button wire:click="closeBlockModal"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <x-heroicon-o-plus class="w-6 h-6 text-pink-500" />
            {{ __('Add Block') }}
        </h2>

        <!-- Search filter using Livewire -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            </div>
            <input type="text" wire:model.live="blockFilter"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-pink-500 focus:border-pink-500 block w-full ps-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-pink-500 dark:focus:border-pink-500"
                placeholder="{{ __('Search blocks...') }}">
        </div>

        <!-- Scrollable blocks grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 h-[40vh] overflow-auto">
            <!-- Fallback rendering for debugging -->
            @foreach ($allBlocks as $block)
            <button wire:click="addBlockToModalRow('{{ $block['alias'] }}', {{ isset($block['blockPageName']) ? '\''.$block['blockPageName'].'\'' : 'null' }})"
                class="group h-30 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 p-5 flex flex-col items-center text-center focus:outline-none focus:ring-2 focus:ring-pink-200">
                <div class="w-10 h-10 mb-3 text-pink-500 group-hover:text-pink-600 transition-colors">
                    <x-heroicon-o-cube class="w-10 h-10" />
                </div>
                <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1 text-base">{{ $block['label'] }}</div>
            </button>
            @endforeach

            <!-- Empty state when no blocks -->
            @if (count($allBlocks) === 0)
            <div class="col-span-full text-gray-400 text-center py-8">
                {{ __('No blocks available') }}
            </div>
            @endif
        </div>
    </div>
</div>