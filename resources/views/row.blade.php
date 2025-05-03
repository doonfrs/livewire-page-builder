<div class="grid grid-cols-12 mb-2 border border-gray-300 rounded">
    <div x-data="{ selected: false }" class="{{ $cssClasses }} group">
        <div
            class="block-row relative transition-all duration-300 ease-in-out"
            :class="selected ? 'border-blue-500' : 'border-gray-300'"
            x-on:row-selected.window="selected = $event.detail.rowId == '{{$rowId}}'"
            x-on:block-selected.window="selected = false">
            <div class="absolute top-[-18px] left-1/2 -translate-x-1/2 bg-white/90 border border-gray-200 shadow-xl px-2 py-1 rounded-full flex items-center space-x-1 z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none group-hover:pointer-events-auto">
                <button wire:click="openBlockModal()" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:text-black hover:bg-gray-100 rounded-full transition-colors duration-150" title="Add Block">
                    <x-heroicon-o-plus class="w-5 h-5" />
                </button>
                <button
                    wire:click="rowSelected()"
                    class="w-7 h-7 flex items-center justify-center text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-full transition-colors duration-150"
                    title="Select Row">
                    <x-heroicon-o-cursor-arrow-rays class="w-5 h-5" />
                </button>
                <button
                    wire:click="moveRowUp()"
                    class="w-7 h-7 flex items-center justify-center text-gray-600 hover:text-black hover:bg-gray-100 rounded-full transition-colors duration-150"
                    title="Move Row Up"
                >
                    <x-heroicon-o-arrow-up class="w-5 h-5" />
                </button>
                <button
                    wire:click="moveRowDown()"
                    class="w-7 h-7 flex items-center justify-center text-gray-600 hover:text-black hover:bg-gray-100 rounded-full transition-colors duration-150"
                    title="Move Row Down"
                >
                    <x-heroicon-o-arrow-down class="w-5 h-5" />
                </button>
            </div>

            <div class="{{ count($blocks) == 0 ? 'pt-4 pb-4' : '' }}">
                <div class="row-blocks grid grid-cols-12">
                    @foreach($blocks as $blockId => $block)
                    @livewire('builder-block', [
                    'blockAlias' => $block['alias'],
                    'blockId' => $blockId,
                    ], key($blockId))
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>