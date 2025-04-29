<div>
    <div class="flex items-center justify-between bg-gray-200 shadow-md p-3 text-gray-900">
        <div class="flex gap-2">
            <button class="w-8 h-8 p-1 hover:bg-gray-300 flex items-center justify-center" wire:click="addRow">
                <x-heroicon-o-plus />
            </button>
            <button class="w-8 h-8 p-1 hover:bg-gray-300 flex items-center justify-center">
                <x-heroicon-o-trash />
            </button>
        </div>
    </div>

    <!-- Modal for Adding Block -->
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
                    wire:click="addBlockToModalRow('{{ $block['alias'] }}')">
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

    <div class="flex flex-1 overflow-hidden">
        <div class="w-64">
            @livewire('block-properties')
        </div>

        <main id="main" class="flex-1 p-6 bg-gray-50 overflow-auto">
            @foreach($rows as $rowId=>$row)
            @livewire('row-block', ['rowId' => $rowId], key($rowId))
            @endforeach
        </main>
    </div>



    {{--
        Tailwind safelist:
        col-span-1 col-span-2 col-span-3 col-span-4 col-span-5 col-span-6 col-span-7 col-span-8 col-span-9 col-span-10 col-span-11 col-span-12
        md:col-span-1 md:col-span-2 md:col-span-3 md:col-span-4 md:col-span-5 md:col-span-6 md:col-span-7 md:col-span-8 md:col-span-9 md:col-span-10 md:col-span-11 md:col-span-12
        lg:col-span-1 lg:col-span-2 lg:col-span-3 lg:col-span-4 lg:col-span-5 lg:col-span-6 lg:col-span-7 lg:col-span-8 lg:col-span-9 lg:col-span-10 lg:col-span-11 lg:col-span-12
    --}}
</div>