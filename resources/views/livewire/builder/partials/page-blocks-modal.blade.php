<!-- Modal for Page Blocks -->
<div class="fixed inset-0 z-52 flex items-center justify-center bg-black/40" x-data="{
    blockFilter: '',
    allBlocks: @js($allPageBlocks),
    groupedBlocks: @js($groupedPageBlocks),
    filteredBlocks: function() {
        if (!this.blockFilter.trim()) {
            return this.allBlocks;
        }

        const searchTerm = this.blockFilter.toLowerCase();
        return this.allBlocks.filter(block => {
            return block.label.toLowerCase().includes(searchTerm) ||
                (block.alias && block.alias.toLowerCase().includes(searchTerm));
        });
    },
    isRowExpanded: {},
    toggleRow(rowId) {
        this.isRowExpanded[rowId] = !this.isRowExpanded[rowId];
    }
}">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative"
        @click.outside="$wire.closePageBlocksModal()">
        <button wire:click="closePageBlocksModal"
            class="absolute top-3 end-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <x-heroicon-o-list-bullet class="w-6 h-6 text-pink-500" />
            {{ __('Page Blocks') }}
        </h2>

        <!-- Search filter -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            </div>
            <input type="text" x-model="blockFilter"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-pink-500 focus:border-pink-500 block w-full ps-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-pink-500 dark:focus:border-pink-500"
                placeholder="{{ __('Search blocks...') }}">
        </div>

        <!-- Scrollable blocks list -->
        <div class="h-[40vh] overflow-auto">
            <template x-if="blockFilter.trim() === ''">
                <!-- Tree View (Rows with nested blocks) -->
                <div>
                    <template x-if="Object.keys(groupedBlocks).length === 0">
                        <div class="text-gray-400 text-center py-8">
                            {{ __('No blocks found') }}
                        </div>
                    </template>

                    <ul class="space-y-4">
                        <template x-for="(row, rowId) in groupedBlocks" :key="rowId">
                            <li class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <!-- Row Header -->
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    @click="toggleRow(rowId)">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="$dispatch('select-row', { rowId: rowId }); $wire.closePageBlocksModal()"
                                            class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-pink-500 bg-white dark:bg-gray-900 rounded-md shadow-sm hover:text-pink-600 hover:scale-105 transition-all">
                                            <x-heroicon-o-rectangle-group class="w-5 h-5" />
                                        </button>
                                        <div class="font-medium">Row <span x-text="rowId.substring(0, 6)"></span></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="row.blocks.length"></span> {{ __('blocks') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <x-heroicon-o-chevron-down
                                            class="w-5 h-5 text-gray-500 transition-transform duration-200"
                                            x-bind:class="isRowExpanded[rowId] ? 'rotate-180' : ''" />
                                    </div>
                                </div>

                                <!-- Blocks in Row (Expandable) -->
                                <div x-show="isRowExpanded[rowId]" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="bg-white dark:bg-gray-900 ps-8 py-2">
                                    <ul class="space-y-2">
                                        <template x-for="block in row.blocks" :key="block.id">
                                            <li>
                                                <button
                                                    @click="$dispatch('select-block', { blockId: block.id }); $wire.closePageBlocksModal()"
                                                    class="w-full group flex items-center gap-3 p-2 border border-gray-100 dark:border-gray-800 rounded-md hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-200 dark:hover:border-pink-700 transition-all duration-200 text-start focus:outline-none focus:ring-2 focus:ring-pink-200">
                                                    <div
                                                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center text-pink-500 group-hover:text-pink-600 transition-colors">
                                                        <div x-html="`<x-${block.icon} class='w-4 h-4' />`"></div>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 group-hover:text-pink-700 dark:group-hover:text-pink-300 transition-colors"
                                                            x-text="block.label"></div>
                                                    </div>
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="blockFilter.trim() !== ''">
                <!-- Flat Search Results -->
                <ul class="space-y-2">
                    <template x-if="filteredBlocks().length === 0">
                        <div class="text-gray-400 text-center py-8">
                            {{ __('No blocks found') }}
                        </div>
                    </template>

                    <template x-for="block in filteredBlocks()" :key="block.id">
                        <li>
                            <button
                                @click="$dispatch('select-block', { blockId: block.id }); $wire.closePageBlocksModal()"
                                class="w-full group flex items-center gap-3 p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-200 dark:hover:border-pink-700 transition-all duration-200 text-start focus:outline-none focus:ring-2 focus:ring-pink-200">
                                <div
                                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-pink-500 group-hover:text-pink-600 transition-colors">
                                    <div x-html="`<x-${block.icon} class='w-6 h-6' />`"></div>
                                </div>
                                <div class="flex-grow">
                                    <div class="font-medium text-gray-800 dark:text-gray-100 group-hover:text-pink-700 dark:group-hover:text-pink-300 transition-colors"
                                        x-text="block.label"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors"
                                        x-text="block.alias"></div>
                                </div>
                            </button>
                        </li>
                    </template>
                </ul>
            </template>
        </div>
    </div>
</div>
