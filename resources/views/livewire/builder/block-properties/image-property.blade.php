<div>
    <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
        <span>{{ $property['label'] }}</span>
    </label>
    <div class="mt-1">
        <!-- Image preview -->
        <div class="border border-gray-300 rounded bg-gray-100 h-24 flex items-center justify-center dark:bg-gray-800 dark:border-gray-700 relative group overflow-hidden">
            @if(!empty($properties[$property['name']]))
                <img src="{{ $properties[$property['name']] }}" class="h-full w-full object-cover" alt="{{ $property['label'] }}" />
                <button 
                    class="absolute top-1 right-1 hidden group-hover:flex p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none"
                    title="{{ __('Remove image') }}"
                    wire:click="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', null)">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            @else
                <x-heroicon-o-photo class="w-8 h-8 text-gray-400 dark:text-gray-600" />
            @endif
        </div>
        <!-- Image selector button -->
        <div class="mt-2 flex justify-between items-center">
            <input 
                type="file" 
                id="file-{{ $property['name'] }}" 
                class="hidden" 
                accept="image/*" 
                wire:model="uploadedImage"
                wire:change.debounce.500ms="uploadImage('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}')" />
            <button
                type="button"
                class="w-full inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-800"
                x-on:click="document.getElementById('file-{{ $property['name'] }}').click()">
                <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1" />
                {{ __('Upload Image') }}
            </button>
        </div>
    </div>
</div> 