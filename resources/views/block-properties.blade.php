<aside class="fixed top-[56px] right-0 h-[calc(100vh-56px)] w-72 bg-white border-l border-gray-200 shadow-lg overflow-y-auto z-20">
    <!-- Header -->
    <div class="sticky top-0 bg-gradient-to-r from-gray-800 to-gray-700 text-white px-4 py-3 border-b border-gray-700 shadow-md z-10">
        <h2 class="text-lg font-medium flex items-center">
            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 mr-2" />
            Properties
        </h2>
        <div class="mt-1 text-xs font-mono bg-gray-900/50 rounded px-2 py-1 truncate">
            {{ $blockLabel ?? 'No block selected' }}
        </div>
    </div>

    <!-- Empty State -->
    @if(empty($blockProperties) || empty($properties))
    <div class="flex flex-col items-center justify-center h-64 text-center p-6">
        <x-heroicon-o-square-3-stack-3d class="w-12 h-12 text-gray-300 mb-3" />
        <div class="text-gray-500 font-medium">No properties available</div>
        <div class="text-gray-400 text-sm mt-1">Select a block to view and edit its properties</div>
    </div>
    @else

    <!-- Properties Groups -->
    <div class="divide-y divide-gray-100">
        <!-- General Properties -->
        <div class="p-4">
            <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Block Settings</div>

            <div class="space-y-4">
                @foreach($blockProperties as $property)
                @if(!in_array($property['name'], ['mobile_grid_size', 'tablet_grid_size', 'desktop_grid_size']))
                <div wire:key="property-{{ $blockId }}-{{ $property['name'] }}" class="group">
                    @if($property['type'] === 'checkbox')
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="property-{{ $property['name'] }}"
                            class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150 ease-in-out border-gray-300 focus:ring-2 focus:ring-blue-200"
                            @if(($properties[$property['name']] ?? false)) checked @endif
                            wire:change.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.checked)">
                        <label
                            for="property-{{ $property['name'] }}"
                            class="ml-2 ms-2 text-sm font-medium text-gray-700 cursor-pointer">
                            {{ $property['label'] }}
                        </label>
                    </div>
                    @else
                    <label class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                        <span>{{ $property['label'] }}</span>
                        <span class="text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">{{ $property['name'] }}</span>
                    </label>
                    <input
                        type="text"
                        class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200"
                        value="{{ $properties[$property['name']] ?? '' }}"
                        wire:input.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.value)">
                    @endif

                    @if(isset($property['description']))
                    <p class="mt-1 text-xs text-gray-500">{{ $property['description'] }}</p>
                    @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <!-- Responsive Settings -->
        @if(isset($properties['mobile_grid_size']) || isset($properties['tablet_grid_size']) || isset($properties['desktop_grid_size']))
        <div class="p-4 bg-gray-50">
            <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 flex items-center">
                <x-heroicon-o-device-phone-mobile class="w-4 h-4 mr-1" />
                Responsive
            </div>

            <!-- Separate responsive properties if needed -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mobile</label>
                    <input
                        type="number"
                        class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-gray-300"
                        value="{{ $properties['mobile_grid_size'] ?? 12 }}"
                        wire:input.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', 'mobile_grid_size', $event.target.value)">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tablet</label>
                    <input
                        type="number"
                        class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-gray-300"
                        value="{{ $properties['tablet_grid_size'] ?? 12 }}"
                        wire:input.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', 'tablet_grid_size', $event.target.value)">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Desktop</label>
                    <input
                        type="number"
                        class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-gray-300"
                        value="{{ $properties['desktop_grid_size'] ?? 12 }}"
                        wire:input.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', 'desktop_grid_size', $event.target.value)">
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</aside>