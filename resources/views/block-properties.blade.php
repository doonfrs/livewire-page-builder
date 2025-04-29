<aside class="bg-white p-4 border-r border-gray-300 shadow-md overflow-y-auto h-lvh">
    <h2 class="text-lg font-semibold mb-4">Block Properties</h2>

    <!-- Debug Information -->
    <div class="mb-4 text-xs p-2 bg-gray-100 rounded">
        <div>Row ID: {{ $rowId ?? 'None' }}</div>
        <div>Block ID: {{ $blockId ?? 'None' }}</div>
    </div>

    <div class="space-y-3">
        @foreach($blockProperties as $property)
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ $property['label'] }}</label>
            @if($property['type'] === 'checkbox')
            <input
                type="checkbox"
                class="form-checkbox"
                @if(($properties[$property['name']] ?? false)) checked @endif
                wire:change.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.checked)">
            @else
            <input
                type="text"
                class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-gray-300"
                value="{{ $properties[$property['name']] ?? '' }}"
                wire:input.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.value)">
            @endif
        </div>
        @endforeach
        @if(empty($properties))
        <div class="text-gray-400">No properties defined for this block.</div>
        @endif
    </div>
</aside>