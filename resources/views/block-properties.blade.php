<aside class="bg-white p-4 border-r border-gray-300 shadow-md overflow-y-auto h-lvh">
    <h2 class="text-lg font-semibold mb-4">Block Properties</h2>

    <!-- Debug Information -->
    <div class="mb-4 text-xs p-2 bg-gray-100 rounded">
        <div>Row ID: {{ $debug['selectedRowId'] ?? 'None' }}</div>
        <div>Block ID: {{ $debug['selectedBlockId'] ?? 'None' }}</div>
        <div>Block Class: {{ $debug['blockClass'] ?? 'None' }}</div>
        <div>Block Data: {{ $debug['blockData'] ?? 'None' }}</div>
    </div>

    @php
    $properties = [];
    if ($blockClass && class_exists($blockClass)) {
    $instance = app($blockClass);
    $properties = $instance->getPageBuilderProperties();
    }
    @endphp
    <div class="space-y-3">
        @foreach($properties as $property)
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ $property->label }}</label>
            @if($property->getType() === 'checkbox')
            <input type="checkbox" class="form-checkbox" @if(($blockData['properties'][$property->name] ?? false)) checked @endif disabled>
            @else
            <input type="text" class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-gray-300" value="{{ $blockData['properties'][$property->name] ?? '' }}" disabled>
            @endif
        </div>
        @endforeach
        @if(empty($properties))
        <div class="text-gray-400">No properties defined for this block.</div>
        @endif
    </div>
</aside>