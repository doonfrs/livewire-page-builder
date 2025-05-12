@props([
    'property',
    'properties',
    'rowId',
    'blockId'
])

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
        <span>{{ $property['label'] }}</span>
    </label>
    <select
        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
        wire:change.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.value)">
        @foreach($property['options'] as $value => $label)
            <option 
                value="{{ $value }}" 
                @if(($properties[$property['name']] ?? $property['defaultValue'] ?? '') == $value) selected @endif
            >
                {{ $label }}
            </option>
        @endforeach
    </select>
</div> 