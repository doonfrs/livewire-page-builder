<div>
    <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
        <span>{{ $propertyLabel }}</span>
    </label>
    <div class="mt-1">
        <!-- Image preview -->
        <div class="border border-gray-300 rounded bg-gray-100 h-24 flex items-center justify-center dark:bg-gray-800 dark:border-gray-700 relative group overflow-hidden">
            @if(!empty($currentValue))
                <img src="{{ $currentValue }}" class="h-full w-full object-cover" />
                <button 
                    class="absolute top-1 right-1 hidden group-hover:flex p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none"
                    title="{{ __('Remove image') }}"
                    wire:click="removeImage()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @else
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            @endif
        </div>
        <!-- Image selector button -->
        <div class="mt-2 flex justify-between items-center">
            <input 
                type="file" 
                id="file-{{ $propertyName }}" 
                class="hidden" 
                accept="image/*" 
                wire:model="uploadedImage"
                wire:change.debounce.500ms="uploadImage()" />
            <button
                type="button"
                class="w-full inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-800"
                x-on:click="document.getElementById('file-{{ $propertyName }}').click()">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                {{ __('Upload Image') }}
            </button>
        </div>
    </div>
</div> 