<div class="block-row relative" id="block-row-{{ $rowId }}">
    <!-- Icons (Positioned Outside the Row) -->
    <div class="absolute top-[-14px] left-1/2 transform -translate-x-1/2 bg-white shadow-md px-2 py-1 rounded-full flex space-x-2 z-10">
        <!-- Add Row -->
        <button wire:click="addRow" class="p-1">
            <svg class="w-5 h-5 text-gray-600 hover:text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
        </button>

        <!-- Move (Draggable) -->
        <button class="p-1 cursor-grab handle">
            <svg class="w-5 h-5 text-gray-600 hover:text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6h6M9 12h6m-6 6h6"></path>
            </svg>
        </button>

        <!-- Delete Row -->
        <button wire:click="deleteRow('{{ $rowId }}')" class="p-1">
            <svg class="w-5 h-5 text-gray-600 hover:text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Row Container -->
    <div class="border border-gray-300 p-3 mb-2 bg-gray-100" wire:click.self="rowSelected('{{ $rowId }}')">
        <div class="mt-2">
            <h6 class="text-sm font-semibold">Select a Widget:</h6>
            @foreach($availableWidgets as $widget)
            <button class="border border-gray-500 text-gray-600 hover:bg-gray-600 hover:text-white text-sm px-3 py-1 rounded m-1" wire:click="addWidget('{{ $widget['name'] }}')">
                {{ $widget['name'] }}
            </button>
            @endforeach
        </div>

        <div class="row-widgets" id="row-widgets-{{ $rowId }}">
            @foreach($blocks as $blockId => $block)
            <div class="block-widget" id="block-widget-{{ $blockId }}">
                <h5 class="text-lg font-semibold">Widget: {{ $block['name'] }}</h5>
                @livewire('block', [
                'blockName' => $block['name'],
                'blockId' => $blockId,
                ], key($blockId))
            </div>
            @endforeach
        </div>
    </div>
</div>