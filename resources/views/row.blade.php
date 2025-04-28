<div class="block-row relative" id="block-row-{{ $rowId }}">
    <!-- Icons (Positioned Outside the Row) -->
    <div class="absolute top-[-14px] left-1/2 transform -translate-x-1/2 bg-white shadow-md px-2 py-1 rounded-full flex space-x-2 z-10">
        <!-- Add Block Button -->
        <button wire:click="openBlockModal" class="w-8 h-8 text-gray-600 hover:text-black p-1" title="Add Block">
            <x-heroicon-o-plus />
        </button>
        <!-- (Other row controls can go here) -->
    </div>

    <!-- Modal -->
    @if($showBlockModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button wire:click="closeBlockModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h2 class="text-lg font-semibold mb-4">Add Block</h2>
            <input type="text" wire:model="blockFilter" placeholder="Search blocks..." class="w-full border rounded px-3 py-2 mb-4" />
            <div class="max-h-64 overflow-y-auto">
                @forelse($this->filteredBlocks as $block)
                <button class="flex items-center w-full px-3 py-2 mb-2 border rounded hover:bg-gray-100"
                    wire:click="addBlock('{{ $block['alias'] }}')">
                    <x-dynamic-component :component="$block['icon']" class="w-5 h-5 mr-2" />
                    <span>{{ $block['label'] }}</span>
                </button>
                @empty
                <div class="text-gray-400 text-center py-4">No blocks found.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- Row Container -->
    <div class="border border-gray-300 p-3 mb-2 bg-gray-100" wire:click.self="rowSelected('{{ $rowId }}')">
        <div class="mt-2">
            <h6 class="text-sm font-semibold">Blocks in this row:</h6>
        </div>
        <div class="row-blocks" id="row-blocks-{{ $rowId }}">
            @foreach($blocks as $blockId => $block)
            @if(is_array($block) && isset($block['alias']))
            <div class="cursor-pointer" wire:click="selectBlock('{{ $blockId }}')">
                @livewire('block', [
                'blockName' => $block['alias'],
                'blockId' => $blockId,
                ], key($blockId))
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>