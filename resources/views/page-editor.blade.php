<div>
    <div class="flex items-center justify-between bg-gray-200 shadow-md p-3 text-gray-900">
        <div class="flex gap-2">
            <button class="w-8 h-8 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 flex items-center justify-center" wire:click="addRow">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </button>
            <button class="w-8 h-8 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 flex items-center justify-center">
                <i class="fas fa-plus"></i>
            </button>
            <button class="w-8 h-8 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 flex items-center justify-center">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>


    <div class="flex flex-1 overflow-hidden">
        <div class="w-64">
            @livewire('block-properties')
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



    <!-- Custom JavaScript functions -->
    <script>
        // Define global function for isContainer
        function customIsContainer(el) {
            // Only consider an element a container if it has the 'droppable' class
            return el.classList.contains('droppable');
        }

        // Define global function for moves
        function customMoves(el, source, handle, sibling) {
            // Don't allow dragging if it's within a locked container
            if (source.classList.contains('locked-container')) {
                return false;
            }

            // Only allow dragging by handle
            return handle.classList.contains('drag-handle');
        }

        // Define global function for accepts
        function customAccepts(el, target, source, sibling) {
            // Don't allow dropping in targets with 'no-drop' class
            if (target.classList.contains('no-drop')) {
                return false;
            }

            // Only allow dropping items with the same type
            const targetType = target.dataset.type;
            const elementType = el.dataset.type;

            return !targetType || !elementType || targetType === elementType;
        }

        // Define event handlers (optional)
        function onDrop(el, target, source, sibling) {
            // Custom drop handler - this will run in addition to the Livewire event
            console.log('Custom drop handler:', el.id, 'dropped in', target.id);

            // Maybe show a success notification
            if (window.showNotification) {
                window.showNotification('Item dropped successfully!');
            }
        }
    </script>

    @livewire('dragula-widget', [
    'containerIds' => ['main'],
    'options' => [
    'direction' => 'vertical',
    'removeOnSpill' => false,
    /*'isContainer' => 'customIsContainer',
    'moves' => 'customMoves',
    'accepts' => 'function(){
    return true;
    }',*/
    ]
    ])
</div>