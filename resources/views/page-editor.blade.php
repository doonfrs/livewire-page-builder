<div>
    <!-- Debug message -->
    @if (session()->has('debug'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-2" role="alert">
        {{ session('debug') }}
    </div>
    @endif

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


    <div class="flex flex-1 overflow-hidden">
        <div class="w-64">
            @livewire('block-properties', [
            'selectedRowId' => $selectedRowId,
            'selectedBlockId' => $selectedBlockId,
            'blockData' => $this->selectedBlockData,
            'blockClass' => $this->selectedBlockClass,
            ], key($selectedRowId . '-' . $selectedBlockId))
        </div>

        <main id="main" class="flex-1 p-6 bg-gray-50 overflow-auto">
            @foreach($rows as $rowId=>$row)
            @livewire('row',
            [
            'blocks' => $row['blocks'],
            'rowId' => $rowId,
            ],
            key($rowId))
            @endforeach
        </main>
    </div>
</div>