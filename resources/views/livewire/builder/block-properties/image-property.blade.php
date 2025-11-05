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
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            @else
                <x-heroicon-o-photo class="w-8 h-8 text-gray-400 dark:text-gray-600" />
            @endif
        </div>

        <!-- URL input field -->
        <div class="mt-2">
            <input
                type="text"
                wire:model.blur="currentValue"
                wire:change="updateImageUrl()"
                placeholder="{{ __('Enter image URL') }}"
                class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />
        </div>

        <!-- Image upload button -->
        <div class="mt-2 flex justify-between items-center">
            <input
                type="file"
                id="file-{{ $propertyName }}"
                class="hidden"
                accept="image/*"
                wire:model.live="uploadedImage" />
            <button
                type="button"
                class="w-full inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="uploadedImage"
                x-on:click="document.getElementById('file-{{ $propertyName }}').click()">
                <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1" />
                <span wire:loading.remove wire:target="uploadedImage">{{ __('Upload Image') }}</span>
                <span wire:loading wire:target="uploadedImage">{{ __('Uploading...') }}</span>
            </button>
        </div>

        <!-- Upload progress indicator -->
        <div wire:loading wire:target="uploadedImage" class="mt-2">
            <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-1.5 rounded-full animate-pulse" style="width: 100%"></div>
            </div>
        </div>

        <!-- Validation errors -->
        @error('uploadedImage')
            <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                {{ $message }}
            </div>
        @enderror
    </div>
</div> 