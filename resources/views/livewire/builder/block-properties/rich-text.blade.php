<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
        @if(isset($propertyLabel))
            <x-heroicon-o-document-text class="inline w-4 h-4 mr-1 align-text-bottom text-gray-400 dark:text-gray-500" />
            {{ $propertyLabel }}
        @endif
    </label>
    <textarea
        wire:model.live="currentValue"
        class="w-full min-h-[120px] rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
        placeholder="Enter rich text..."
    ></textarea>
</div> 