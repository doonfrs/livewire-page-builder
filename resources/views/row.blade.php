<div class="block-row relative" id="block-row-{{ $rowId }}">
    <!-- Icons (Positioned Outside the Row) -->
    <div class="absolute top-[-14px] left-1/2 transform -translate-x-1/2 bg-white shadow-md px-2 py-1 rounded-full flex space-x-2 z-10">
        <!-- Add Row -->
        <button wire:click="addRow"
            class="w-8 h-8 text-gray-600 hover:text-black p-1">
            <x-heroicon-o-plus />
        </button>

        <!-- Move (Draggable) -->
        <button class="w-8 h-8 text-gray-600 hover:text-black p-1 cursor-grab handle">
            <x-heroicon-o-arrows-up-down />
        </button>

        <!-- Delete Row -->
        <button wire:click="deleteRow('{{ $rowId }}')"
            class="w-8 h-8 text-gray-600 hover:text-black p-1">
            <x-heroicon-o-trash />
        </button>
    </div>

    <!-- Row Container -->
    <div class="border border-gray-300 p-3 mb-2 bg-gray-100" wire:click.self="rowSelected('{{ $rowId }}')">
        <div class="mt-2">
            <h6 class="text-sm font-semibold">Select a Block:</h6>
            @foreach($availableBlocks as $block)
            <button class="border border-gray-500 text-gray-600 hover:bg-gray-600 hover:text-white text-sm px-3 py-1 rounded m-1"
                wire:click="addBlock('{{  $block['alias'] }}')">
                <x-dynamic-component :component="$block['icon']" class="inline w-4 h-4 mr-1" />
                {{ $block['label'] }}
            </button>
            @endforeach
        </div>

        <div class="row-blocks" id="row-blocks-{{ $rowId }}">
            @foreach($blocks as $blockId => $block)
            <div class="block-block" id="block-block-{{ $blockId }}">
                <h5 class="text-lg font-semibold">Block: {{ $block['alias'] }}</h5>
                @livewire('block', [
                'blockName' => $block['alias'],
                'blockId' => $blockId,
                ], key($blockId))
            </div>
            @endforeach
        </div>
    </div>
</div>