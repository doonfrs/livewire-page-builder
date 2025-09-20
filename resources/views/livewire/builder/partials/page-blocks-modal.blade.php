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
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            {{ __('Page Blocks') }}
        </h2>

        <!-- Search filter -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
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
                                        <button wire:click="$dispatch('select-row', { rowId: rowId })"
                                            @click="$wire.closePageBlocksModal()"
                                            class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-pink-500 bg-white dark:bg-gray-900 rounded-md shadow-sm hover:text-pink-600 hover:scale-105 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />
                                            </svg>
                                        </button>
                                        <div class="font-medium">Row <span x-text="rowId.substring(0, 6)"></span></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="row.blocks.length"></span> {{ __('blocks') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" x-bind:class="isRowExpanded[rowId] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
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
                                                <button wire:click="$dispatch('select-block', { blockId: block.id })"
                                                    @click="$wire.closePageBlocksModal()"
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
                            <button wire:click="$dispatch('select-block', { blockId: block.id })"
                                @click="$wire.closePageBlocksModal()"
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
