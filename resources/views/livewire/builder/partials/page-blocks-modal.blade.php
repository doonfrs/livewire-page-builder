<!-- Modal for Page Blocks -->
<div class="fixed inset-0 z-52 flex items-center justify-center bg-black/40"
    x-data="{
    blockFilter: '',
    allBlocks: @js($allPageBlocks),
    groupedBlocks: @js($groupedPageBlocks),
    selectedBlockId: null,
    selectedRowId: null,
    getParentSelection() {
        // Access the parent Alpine component's data
        let parent = this.$el;
        while (parent) {
            parent = parent.parentElement;
            if (parent && parent._x_dataStack) {
                for (let data of parent._x_dataStack) {
                    if (data.currentSelectedBlockId !== undefined || data.currentSelectedRowId !== undefined) {
                        return {
                            blockId: data.currentSelectedBlockId,
                            rowId: data.currentSelectedRowId
                        };
                    }
                }
            }
        }
        return { blockId: null, rowId: null };
    },
    init() {
        // Get initial selection from parent scope
        const selection = this.getParentSelection();
        this.selectedBlockId = selection.blockId;
        this.selectedRowId = selection.rowId;

        console.log('Modal initialized with selection:', {
            selectedBlockId: this.selectedBlockId,
            selectedRowId: this.selectedRowId,
            groupedBlockKeys: Object.keys(this.groupedBlocks)
        });

        // Initialize all rows as collapsed
        this.$nextTick(() => {
            Object.keys(this.groupedBlocks).forEach(rowId => {
                this.isRowExpanded[rowId] = false;
            });
            this.allBlocks.forEach(block => {
                if (block.nestedBlocks && block.nestedBlocks.length > 0) {
                    this.isRowExpanded[block.id] = false;
                }
            });

            // Expand parent rows if a block is selected
            if (this.selectedBlockId) {
                console.log('Expanding parent rows for block:', this.selectedBlockId);
                this.expandParentRows(this.selectedBlockId);
            }
            // Expand row if it's selected (including nested rows)
            if (this.selectedRowId) {
                console.log('Expanding and highlighting selected row:', this.selectedRowId);
                // Expand the selected row itself
                this.isRowExpanded[this.selectedRowId] = true;
                // Also expand parent rows if this is a nested row
                this.expandParentRows(this.selectedRowId);
            }

            // Scroll to selected element after a short delay to allow DOM updates
            setTimeout(() => {
                let selectedElement = null;

                // Try to find by looking for pink or blue highlighted elements
                // Nested rows have ring-2 class
                selectedElement = this.$el.querySelector('.ring-2.ring-pink-500');

                if (!selectedElement) {
                    selectedElement = this.$el.querySelector('.ring-2.ring-blue-500');
                }

                // If not found, try regular blocks (border-blue-500)
                if (!selectedElement) {
                    selectedElement = this.$el.querySelector('.border-blue-500');
                }

                // If not found, try top-level rows (border-pink-500)
                if (!selectedElement) {
                    selectedElement = this.$el.querySelector('.border-pink-500');
                }

                if (selectedElement) {
                    const scrollContainer = this.$el.querySelector('.overflow-auto');
                    if (scrollContainer) {
                        // Get the position of the element relative to the scroll container
                        const elementRect = selectedElement.getBoundingClientRect();
                        const containerRect = scrollContainer.getBoundingClientRect();
                        const relativeTop = elementRect.top - containerRect.top;
                        const targetScroll = scrollContainer.scrollTop + relativeTop - (containerRect.height / 2) + (elementRect.height / 2);

                        scrollContainer.scrollTo({
                            top: targetScroll,
                            behavior: 'smooth'
                        });
                        console.log('Scrolled to selected element');
                    }
                }
            }, 300);
        });
    },
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
    },
    findBlockParents(blockId, blocks = null, parentRowId = null, path = []) {
        // Search through grouped blocks if no blocks provided
        if (blocks === null) {
            for (const [rowId, row] of Object.entries(this.groupedBlocks)) {
                const result = this.findBlockParents(blockId, row.blocks, rowId, [rowId]);
                if (result) return result;
            }
            return null;
        }

        // Search through the current blocks array
        for (const block of blocks) {
            if (block.id === blockId) {
                return { parentRowId, path };
            }
            // Check nested blocks recursively
            if (block.nestedBlocks && block.nestedBlocks.length > 0) {
                const result = this.findBlockParents(blockId, block.nestedBlocks, block.id, [...path, block.id]);
                if (result) return result;
            }
        }
        return null;
    },
    expandParentRows(blockId) {
        const result = this.findBlockParents(blockId);
        if (result && result.path) {
            // Expand all parent rows in the path
            result.path.forEach(rowId => {
                this.isRowExpanded[rowId] = true;
            });
        }
    },
    handleBlockSelected(event) {
        this.selectedBlockId = event.detail.blockId;
        this.selectedRowId = null;
        // Expand parent rows to make the block visible
        this.expandParentRows(event.detail.blockId);
    },
    handleRowSelected(event) {
        this.selectedRowId = event.detail.rowId;
        this.selectedBlockId = null;
        // Expand the row if it's collapsed
        if (this.isRowExpanded[event.detail.rowId] === false) {
            this.isRowExpanded[event.detail.rowId] = true;
        }
    },
    renderBlockItem(block, depth = 0) {
        const indent = depth * 16;
        const hasNested = block.nestedBlocks && block.nestedBlocks.length > 0;

        if (hasNested) {
            // This is a nested row
            return {
                type: 'row',
                block: block,
                indent: indent,
                hasNested: true
            };
        } else {
            // Regular block
            return {
                type: 'block',
                block: block,
                indent: indent,
                hasNested: false
            };
        }
    },
    getAllNestedBlocks(blocks, depth = 0, parentExpanded = true) {
        let result = [];
        blocks.forEach(block => {
            const item = this.renderBlockItem(block, depth);
            item.visible = parentExpanded; // Visible only if parent is expanded
            result.push(item);

            // If this has nested blocks, recurse
            if (block.nestedBlocks && block.nestedBlocks.length > 0) {
                // Check if this row is expanded (use the actual value, default to true)
                const isExpanded = this.isRowExpanded[block.id] === true;
                const nestedItems = this.getAllNestedBlocks(
                    block.nestedBlocks,
                    depth + 1,
                    parentExpanded && isExpanded // Only visible if both parent and this row are expanded
                );
                result = result.concat(nestedItems);
            }
        });
        return result;
    }
}">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative"
        @click.outside="$wire.closePageBlocksModal()"
        x-on:block-selected.window="handleBlockSelected($event)"
        x-on:row-selected.window="handleRowSelected($event)">
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
                            <li class="border rounded-lg overflow-hidden transition-colors"
                                :class="selectedRowId === rowId ? 'border-pink-500 bg-pink-50/50 dark:bg-pink-900/20' : 'border-gray-200 dark:border-gray-700'">
                                <!-- Row Header -->
                                <div class="p-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    :class="selectedRowId === rowId ? 'bg-pink-100 dark:bg-pink-900/30' : 'bg-gray-100 dark:bg-gray-800'"
                                    @click="toggleRow(rowId)">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-rectangle-group class="w-5 h-5 text-blue-500" />
                                        <div class="font-medium">{{ __('Row') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="row.blocks.length"></span> {{ __('blocks') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <!-- Darker select button -->
                                        <button
                                            wire:click="$dispatch('select-row', { rowId: rowId })"
                                            @click.stop="$wire.closePageBlocksModal()"
                                            class="p-1.5 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-all"
                                            title="{{ __('Select entire row') }}">
                                            <x-heroicon-o-cursor-arrow-rays class="w-4 h-4" />
                                        </button>
                                        <x-heroicon-o-chevron-down
                                            class="w-5 h-5 text-gray-500 transition-transform duration-200"
                                            x-bind:class="isRowExpanded[rowId] ? 'rotate-180' : ''" />
                                    </div>
                                </div>

                                <!-- Blocks in Row (Expandable with recursive nesting) -->
                                <div x-show="isRowExpanded[rowId]" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="bg-white dark:bg-gray-900 ps-4 pe-4 py-2">

                                    <!-- Recursive Block List -->
                                    <template x-for="item in getAllNestedBlocks(row.blocks)" :key="item.block.id">
                                        <div x-show="item.visible" :style="`margin-left: ${item.indent}px`" class="my-2">
                                            <!-- Nested Row -->
                                            <template x-if="item.hasNested">
                                                <div>
                                                    <div
                                                        class="flex items-center gap-2 p-2 rounded-md transition-colors"
                                                        :class="(selectedBlockId === item.block.id || selectedRowId === item.block.id) ? 'bg-pink-100 dark:bg-pink-900/30 ring-2 ring-pink-500/50' : 'bg-gray-50 dark:bg-gray-800'">
                                                        <button @click="toggleRow(item.block.id)"
                                                            class="flex items-center gap-2 flex-1 text-left hover:text-pink-600 transition-colors">
                                                            <x-heroicon-o-chevron-right
                                                                class="w-4 h-4 transition-transform"
                                                                x-bind:class="isRowExpanded[item.block.id] ? 'rotate-90' : ''" />
                                                            <!-- Row icon -->
                                                            <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center text-blue-500">
                                                                <x-heroicon-o-rectangle-group class="w-5 h-5" />
                                                            </div>
                                                            <span class="text-sm font-medium"
                                                                x-text="item.block.label"></span>
                                                            <span class="text-xs text-gray-500">
                                                                (<span x-text="item.block.nestedBlocks.length"></span>
                                                                {{ __('blocks') }})
                                                            </span>
                                                        </button>
                                                        <!-- Darker select button with icon -->
                                                        <button
                                                            wire:click="$dispatch('select-block', { blockId: item.block.id })"
                                                            @click="$wire.closePageBlocksModal()"
                                                            class="p-1.5 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-all"
                                                            title="{{ __('Select this row') }}">
                                                            <x-heroicon-o-cursor-arrow-rays class="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Regular Block -->
                                            <template x-if="!item.hasNested">
                                                <button
                                                    wire:click="$dispatch('select-block', { blockId: item.block.id })"
                                                    @click="$wire.closePageBlocksModal()"
                                                    class="w-full group flex items-center gap-3 p-2 border rounded-md hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-200 dark:hover:border-pink-700 transition-all duration-200 text-start focus:outline-none focus:ring-2 focus:ring-pink-200"
                                                    :class="selectedBlockId === item.block.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-100 dark:border-gray-800'">
                                                    <div
                                                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center text-pink-500 group-hover:text-pink-600 transition-colors">
                                                        <template x-if="item.block.icon === 'heroicon-o-document-text'">
                                                            <x-heroicon-o-document-text class="w-4 h-4" />
                                                        </template>
                                                        <template x-if="item.block.icon === 'heroicon-o-rectangle-stack'">
                                                            <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                                                        </template>
                                                        <template x-if="item.block.icon === 'heroicon-o-rectangle-group'">
                                                            <x-heroicon-o-rectangle-group class="w-4 h-4" />
                                                        </template>
                                                        <template x-if="item.block.icon === 'heroicon-o-cube' || !item.block.icon">
                                                            <x-heroicon-o-cube class="w-4 h-4" />
                                                        </template>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 group-hover:text-pink-700 dark:group-hover:text-pink-300 transition-colors"
                                                            x-text="item.block.label"></div>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
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
                                class="w-full group flex items-center gap-3 p-3 bg-white dark:bg-gray-900 border rounded-lg shadow-sm hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-200 dark:hover:border-pink-700 transition-all duration-200 text-start focus:outline-none focus:ring-2 focus:ring-pink-200"
                                :class="selectedBlockId === block.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                <div
                                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-pink-500 group-hover:text-pink-600 transition-colors">
                                    <template x-if="block.icon === 'heroicon-o-document-text'">
                                        <x-heroicon-o-document-text class="w-6 h-6" />
                                    </template>
                                    <template x-if="block.icon === 'heroicon-o-rectangle-stack'">
                                        <x-heroicon-o-rectangle-stack class="w-6 h-6" />
                                    </template>
                                    <template x-if="block.icon === 'heroicon-o-rectangle-group'">
                                        <x-heroicon-o-rectangle-group class="w-6 h-6" />
                                    </template>
                                    <template x-if="block.icon === 'heroicon-o-cube' || !block.icon">
                                        <x-heroicon-o-cube class="w-6 h-6" />
                                    </template>
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
