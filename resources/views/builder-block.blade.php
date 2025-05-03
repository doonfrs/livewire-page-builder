<div x-data="{ selected: false, showContextMenu: false, x: 0, y: 0 }" class="{{ $cssClasses }}"
    @contextmenu.prevent="
        Livewire.dispatch('show-block-context-menu', {
            blockId: '{{ $blockId }}',
            x: $event.clientX,
            y: $event.clientY
        });
    "
    x-on:show-block-context-menu.window="
        if ($event.detail.blockId === '{{ $blockId }}') {
            showContextMenu = true;
            x = $event.detail.x;
            y = $event.detail.y;
        } else {
            showContextMenu = false;
        }
    "
    @click.outside="showContextMenu = false">
    <div class="block-row relative border transition-all duration-300 ease-in-out "
        :class="selected ? 'border-blue-500' : 'border-gray-300'"
        x-on:block-selected.window="selected = $event.detail.blockId == '{{ $blockId }}'"
        x-on:row-selected.window="selected = false">
        <div class="cursor-pointer" wire:click="blockSelected()">
            <div class="builder-block" style="pointer-events: none">
                @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
            </div>
        </div>
        <!-- Context Menu UI -->
        <div x-show="showContextMenu"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :style="`position: fixed; left: ${x}px; top: ${y}px; z-index: 50;`"
            class="context-menu bg-white border border-gray-200 rounded-lg shadow-lg py-2 w-48">
            <button wire:click="blockSelected(); showContextMenu = false;"
                class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 mr-2 text-gray-500" />
                <span>Select</span>
            </button>
            <button wire:click="$dispatch('deleteBlock', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                onclick="return confirm('Are you sure you want to delete this row?')"
                class="flex items-center w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                <x-heroicon-o-trash class="w-4 h-4 mr-2 text-red-500" />
                <span>Delete</span>
            </button>
            <div class="border-t border-gray-200 my-1"></div>
            <button wire:click="$dispatch('moveBlockUp', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                <x-heroicon-o-arrow-up class="w-4 h-4 mr-2 text-gray-500" />
                <span>Move Up</span>
            </button>
            <button wire:click="$dispatch('moveBlockDown', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                <x-heroicon-o-arrow-down class="w-4 h-4 mr-2 text-gray-500" />
                <span>Move Down</span>
            </button>
        </div>
    </div>
</div>
