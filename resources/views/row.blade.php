<div class="block-row relative" id="block-row-{{ $rowId }}">
    <!-- Icons (Positioned Outside the Row) -->
    <div class="absolute top-[-14px] left-1/2 transform -translate-x-1/2 bg-white shadow-md px-2 py-1 rounded-full flex space-x-2 z-10">
        <!-- Add Block Button -->
        <button wire:click="openBlockModal()" class="w-8 h-8 text-gray-600 hover:text-black p-1" title="Add Block">
            <x-heroicon-o-plus />
        </button>
        <!-- (Other row controls can go here) -->
    </div>

    <!-- Row Container -->
    <div class="border border-gray-300 p-3 mb-2 bg-gray-100" wire:click.self="rowSelected('{{ $rowId }}')">
        <div class="mt-2">
            <h6 class="text-sm font-semibold">Blocks in this row:</h6>
        </div>
        <div class="row-blocks" id="row-blocks-{{ $rowId }}">
            @foreach($blocks as $blockId => $block)
            @if(is_array($block) && isset($block['alias']))
            <div class="cursor-pointer" wire:click="selectBlock('{{ $blockId }}')">
                @livewire('builder-block', [
                'blockName' => $block['alias'],
                'blockId' => $blockId,
                'blockProperties' => $block['propertyValues'] ?? [],
                ], key($blockId))
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>