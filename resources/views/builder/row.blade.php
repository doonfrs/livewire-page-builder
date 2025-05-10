<div x-data="{ selected: false }">
    <div class="block-row border relative transition-all duration-300 ease-in-out group"
        :class="selected ? 'border-pink-500' : 'border-gray-300'"
        x-on:row-selected.window="selected = $event.detail.rowId == '{{ $rowId }}'"
        x-on:block-selected.window="selected = false">
        <!-- Elementor-style Row Controls -->
        <div
            class="absolute top-[-35px] left-1/2 -translate-x-1/2 bg-pink-500 shadow-lg px-1 py-1 rounded-lg flex items-center space-x-1 z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none group-hover:pointer-events-auto">
            <!-- Delete Button -->
            <button wire:click="$dispatch('deleteRow', {rowId: '{{ $rowId }}'})"
                onclick="return confirm('Are you sure you want to delete this row?')"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="Delete Row">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>

            <!-- Handle/Options Button -->
            <button @click="$dispatch('toggle-row-options', {rowId: '{{ $rowId }}'})"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="More Options">
                <x-heroicon-o-ellipsis-horizontal class="w-5 h-5" />
            </button>

            <!-- Add Block Button -->
            <button wire:click="openBlockModal()"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="Add Block">
                <x-heroicon-o-plus class="w-5 h-5" />
            </button>
        </div>

        <!-- Hidden Drawer for Row Tools (appears on click of handle) -->
        <div x-data="{ open: false }"
            x-on:toggle-row-options.window="if($event.detail.rowId === '{{ $rowId }}') open = !open"
            class="absolute top-[-35px] left-1/2 -translate-x-1/2 z-51">
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-2" @click.outside="open = false"
                class="absolute top-[45px] left-1/2 -translate-x-1/2 bg-white shadow-xl rounded-lg border border-gray-200 py-2 w-[200px]">

                <div class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Row Actions
                </div>

                <!-- Row Select Button -->
                <button wire:click="rowSelected()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100"
                    title="Select Row">
                    <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 mr-2" />
                    <span>Select</span>
                </button>

                <!-- Row Move Up Button -->
                <button wire:click="moveRowUp()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100"
                    title="Move Row Up">
                    <x-heroicon-o-arrow-up class="w-4 h-4 mr-2" />
                    <span>Move Up</span>
                </button>

                <!-- Row Move Down Button -->
                <button wire:click="moveRowDown()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100"
                    title="Move Row Down">
                    <x-heroicon-o-arrow-down class="w-4 h-4 mr-2" />
                    <span>Move Down</span>
                </button>
                <!-- Add Row After Button -->
                <button wire:click="$dispatch('addRow', {afterRowId: '{{ $rowId }}'})" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-green-700 hover:bg-green-50"
                    title="Add Row After">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    <span>Add Row After</span>
                </button>

                <!-- Add Row Before Button -->
                <button wire:click="$dispatch('addRow', {beforeRowId: '{{ $rowId }}'})" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-green-700 hover:bg-green-50"
                    title="Add Row Before">
                    <x-heroicon-o-plus-circle class="w-4 h-4 mr-2" />
                    <span>Add Row Before</span>
                </button>
            </div>
        </div>

        <div class="{{ count($blocks) == 0 ? 'pt-4 pb-4' : '' }}">
            <div class="row-blocks {{ $cssClasses }}" style="{{ $inlineStyles }}">
                @foreach ($blocks as $blockId => $block)
                    @livewire(
                        'builder-block',
                        [
                            'blockAlias' => $block['alias'],
                            'blockId' => $blockId,
                            'rowId' => $rowId,
                            'properties' => $block['properties'] ?? [],
                        ],
                        key($blockId)
                    )
                @endforeach
            </div>
        </div>
    </div>
</div>
