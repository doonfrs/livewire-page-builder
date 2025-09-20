<!-- Modal for Adding Block -->
<div class="fixed inset-0 z-52 flex items-center justify-center bg-black/40" x-data="{
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
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ __('Add Block') }}
        </h2>

        <!-- Search filter using Livewire -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input type="text" wire:model.live="blockFilter"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-pink-500 focus:border-pink-500 block w-full ps-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-pink-500 dark:focus:border-pink-500"
                placeholder="{{ __('Search blocks...') }}">
        </div>

        <!-- Scrollable blocks grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 h-[40vh] overflow-auto">
            <!-- Fallback rendering for debugging -->
            @foreach ($allBlocks as $block)
                <button
                    wire:click="addBlockToModalRow('{{ $block['alias'] }}', {{ isset($block['blockPageName']) ? '\'' . $block['blockPageName'] . '\'' : 'null' }})"
                    class="group h-30 border rounded-xl shadow-sm transition-all duration-200 p-5 flex flex-col items-center text-center focus:outline-none focus:ring-2 focus:ring-pink-200 relative {{ isset($block['blockPageName']) ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600 hover:shadow-lg hover:bg-blue-100 dark:hover:bg-blue-900/30' : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 hover:shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    title="{{ isset($block['blockPageName']) ? __('This is a page component that can be reused across themes') : __('Standard content block') }}">
                    <div
                        class="w-10 h-10 mb-3 transition-colors {{ isset($block['blockPageName']) ? 'text-blue-500 group-hover:text-blue-600' : 'text-pink-500 group-hover:text-pink-600' }}">
                        @if (isset($block['blockPageName']))
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        @else
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6.429 9.75 8.571 4.286m0 0 8.571-4.286M15 13.036V21.75l-8.571-4.286M15 13.036l8.571 4.286v8.714L15 21.75M15 13.036 6.429 9.75v8.714L15 21.75" />
                            </svg>
                        @endif
                    </div>
                    <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1 text-base">{{ $block['label'] }}
                    </div>
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
