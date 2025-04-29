<div x-data="{ selected: false }">
    <div
        class="block-row relative border "
        :class="selected ? 'border-blue-500' : 'border-gray-300'"
        x-on:row-selected.window="console.log( $event.detail); selected = $event.detail.rowId == '{{$rowId}}'">
        <div class="absolute top-[-14px] left-1/2 transform -translate-x-1/2 bg-white shadow-md px-2 py-1 rounded-full flex space-x-2 z-10">
            <button wire:click="openBlockModal()" class="w-8 h-8 text-gray-600 hover:text-black p-1" title="Add Block">
                <x-heroicon-o-plus />
            </button>
            <button
                wire:click="rowSelected()"
                class="w-8 h-8 text-blue-600 hover:text-blue-900 p-1"
                title="Select Row">
                <x-heroicon-o-cursor-arrow-rays />
            </button>
        </div>

        <div class="border border-gray-300 p-3 mb-2 bg-gray-100">
            <div class="row-blocks">
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