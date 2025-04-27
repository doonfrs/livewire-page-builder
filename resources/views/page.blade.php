<div class="container mx-auto mt-4">
    <h1 class="text-2xl font-semibold">Page Editor</h1>

    <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded mb-3" wire:click="addRow">
        Add Row
    </button>

    <div>
        @foreach($rows as $rowId => $row)
            @livewire('row', ['blocks' => $row['blocks']], key($rowId))
        @endforeach
    </div>
</div>
