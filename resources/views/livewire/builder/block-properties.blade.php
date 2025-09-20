<div class="h-full overflow-y-auto">
    <!-- Header -->
    <div
        class="sticky top-0 bg-gradient-to-r from-gray-800 to-gray-700 text-white px-4 py-3 border-b border-gray-700 shadow-md">
        <h2 class="text-lg font-medium flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m0 0L7.5 6M7.5 6v0m0 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H3m15.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3.75 0H21" />
            </svg>
            {{ __('Properties') }}
        </h2>
        <div class="mt-1 text-xs font-mono bg-gray-900/50 rounded px-2 py-1 truncate">
            {{ $blockLabel ?? __('No block selected') }}
        </div>
    </div>

    <!-- Empty State -->
    @if (empty($blockProperties) || empty($properties))
        <div class="flex flex-col items-center justify-center h-64 text-center p-6">
            <svg class="w-12 h-12 text-gray-300 mb-3 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6.429 9.75 8.571 4.286m0 0 8.571-4.286M15 13.036V21.75l-8.571-4.286M15 13.036l8.571 4.286v8.714L15 21.75M15 13.036 6.429 9.75v8.714L15 21.75" />
            </svg>
            <div class="text-gray-500 font-medium dark:text-gray-400">{{ __('No properties available') }}</div>
            <div class="text-gray-400 text-sm mt-1 dark:text-gray-500">
                {{ __('Select a block to view and edit its properties') }}</div>
        </div>
    @else
        <!-- Properties Groups -->
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <!-- Property Groups -->
            @foreach ($propertyGroups as $groupName => $group)
                <div class="p-4 @if ($loop->even) bg-gray-50 dark:bg-gray-800/50 @endif">
                    <div
                        class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 flex items-center dark:text-gray-400">
                        <x-dynamic-component :component="$group['icon']" class="w-4 h-4 mr-1" />
                        {{ $group['label'] }}
                    </div>

                    <div class="space-y-4 {{ $group['columns'] > 1 ? 'grid grid-cols-' . $group['columns'] . ' gap-3 space-y-0' : '' }}"
                        wire:key="group-{{ $blockId }}-{{ $groupName }}">
                        @foreach ($group['properties'] as $property)
                            @php
                                $key = ($rowId ?? '') . '-' . ($blockId ?? '') . '-' . $property['name'];
                            @endphp
                            <div wire:key="property-{{ $key }}" class="group">
                                @if ($property['type'] === 'checkbox')
                                    <div class="flex items-center">
                                        <input type="checkbox" id="property-{{ $property['name'] }}"
                                            class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150 ease-in-out border-gray-300 focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:ring-offset-gray-800"
                                            @if ($properties[$property['name']] ?? false) checked @endif
                                            wire:change.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.checked)">
                                        <label for="property-{{ $property['name'] }}"
                                            class="ml-2 ms-2 text-sm font-medium text-gray-700 cursor-pointer dark:text-gray-300">
                                            {{ $property['label'] }}
                                        </label>
                                    </div>
                                @elseif($property['type'] === 'image')
                                    <livewire:block-properties.image-property :property-name="$property['name']" :property-label="$property['label']"
                                        :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :key="'image-property-' . $key" />
                                @elseif($property['type'] === 'color')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
                                            <span>{{ $property['label'] }}</span>
                                        </label>
                                        <livewire:block-properties.color-picker :property-name="$property['name']" :property-label="$property['label']"
                                            :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId"
                                            :key="'color-picker-' . $key" />
                                    </div>
                                @elseif($property['type'] === 'select')
                                    <livewire:block-properties.select-property :property-name="$property['name']" :property-label="$property['label']"
                                        :property-options="$property['options']" :default-value="$property['defaultValue']" :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId"
                                        :block-id="$blockId" :key="'select-property-' . $key" />
                                @elseif($property['type'] === 'richtext')
                                    <livewire:block-properties.richtext-property :property-name="$property['name']" :property-label="$property['label']"
                                        :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :key="'richtext-property-' . $key" />
                                @elseif($property['type'] === 'flexible-size')
                                    <livewire:block-properties.flexible-size-property :property="$property" :value="$properties[$property['name']] ?? ''"
                                        :row-id="$rowId" :block-id="$blockId" :key="'flexible-size-property-' . $key" />
                                @else
                                    <div>
                                        <label
                                            class="flex justify-between text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
                                            <span>{{ $property['label'] }}</span>
                                        </label>
                                        <input type="{{ $property['numeric'] ?? false ? 'number' : 'text' }}"
                                            @if (isset($property['min'])) min="{{ $property['min'] }}" @endif
                                            @if (isset($property['max'])) max="{{ $property['max'] }}" @endif
                                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                                            value="{{ $properties[$property['name']] ?? ($property['defaultValue'] ?? '') }}"
                                            wire:input.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.value)">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
