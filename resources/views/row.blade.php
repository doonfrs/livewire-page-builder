<div class="grid grid-cols-12">
    <div x-data="{ selected: false }" class="{{ $cssClasses }}">
        <div
            class="block-row relative border "
            :class="selected ? 'border-blue-500' : 'border-gray-300'"
            x-on:row-selected.window="selected = $event.detail.rowId == '{{$rowId}}'"
            x-on:block-selected.window="selected = false">
            <div class="absolute top-[-14px] left-1/2 transform -translate-x-1/2 bg-white shadow-md px-1 py-0.5 rounded-full flex space-x-1 z-10">
                <button wire:click="openBlockModal()" class="w-6 h-6 text-gray-600 hover:text-black p-0.5" title="Add Block">
                    <x-heroicon-o-plus class="w-4 h-4" />
                </button>
                <button
                    wire:click="rowSelected()"
                    class="w-6 h-6 text-blue-600 hover:text-blue-900 p-0.5"
                    title="Select Row">
                    <x-heroicon-o-cursor-arrow-rays class="w-4 h-4" />
                </button>
                <button
                    wire:click="moveRowUp()"
                    class="w-6 h-6 text-gray-600 hover:text-black p-0.5"
                    title="Move Row Up"
                >
                    <x-heroicon-o-arrow-up class="w-4 h-4" />
                </button>
                <button
                    wire:click="moveRowDown()"
                    class="w-6 h-6 text-gray-600 hover:text-black p-0.5"
                    title="Move Row Down"
                >
                    <x-heroicon-o-arrow-down class="w-4 h-4" />
                </button>
            </div>

            <div class="border border-gray-300 p-3 mb-6 bg-gray-100">
                <div class="row-blocks" id="row-blocks-{{ $rowId }}">
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