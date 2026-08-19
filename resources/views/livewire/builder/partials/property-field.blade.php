{{--
    One property field. Shared by the builder's property panel and the live edit sheet,
    so the two can never drift apart.

    Expects:
      $property     - the BlockProperty::toArray() schema for this field
      $properties   - current values, keyed by property name
      $rowId        - selected row id, or null
      $blockId      - selected block id, or null
      $key          - unique suffix for wire:key / component keys
      $updateTarget - optional Livewire component name to address property updates to.
                      Null (the builder) leaves the dispatch global, which is what the
                      row/block components in the canvas listen for. Set by the live edit
                      sheet so on-page rows are not dragged into every keystroke.
--}}
@if ($property['type'] === 'checkbox')
    <div>
        <label for="property-{{ $property['name'] }}"
            class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
            {{ $property['label'] }}
        </label>
        <input type="checkbox" id="property-{{ $property['name'] }}"
            class="form-checkbox h-5 w-5 mt-1.5 text-blue-600 rounded transition duration-150 ease-in-out border-gray-300 focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:ring-offset-gray-800"
            @if ($properties[$property['name']] ?? $property['defaultValue'] ?? false) checked @endif
            wire:change.debounce.500ms="updateBlockProperty('{{ $rowId }}', '{{ $blockId }}', '{{ $property['name'] }}', $event.target.checked)">
    </div>
@elseif($property['type'] === 'image')
    <livewire:block-properties.image-property :property-name="$property['name']" :property-label="$property['label']"
        :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null"
        :key="'image-property-' . $key" />
@elseif($property['type'] === 'video')
    <livewire:block-properties.video-property :property-name="$property['name']" :property-label="$property['label']"
        :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null"
        :key="'video-property-' . $key" />
@elseif($property['type'] === 'color')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
            <span>{{ $property['label'] }}</span>
        </label>
        <livewire:block-properties.color-picker :property-name="$property['name']" :property-label="$property['label']"
            :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null"
            :key="'color-picker-' . $key" />
    </div>
@elseif($property['type'] === 'select')
    <livewire:block-properties.select-property :property-name="$property['name']" :property-label="$property['label']"
        :property-options="$property['options']" :default-value="$property['defaultValue']" :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId"
        :block-id="$blockId" :update-target="$updateTarget ?? null" :key="'select-property-' . $key" />
@elseif($property['type'] === 'icon')
    <livewire:block-properties.icon-property :property-name="$property['name']" :property-label="$property['label']"
        :property-styles="$property['styles']" :property-sets="$property['sets']" :default-value="$property['defaultValue']" :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId"
        :block-id="$blockId" :update-target="$updateTarget ?? null" :key="'icon-property-' . $key" />
@elseif($property['type'] === 'richtext')
    <livewire:block-properties.richtext-property :property-name="$property['name']" :property-label="$property['label']"
        :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null"
        :key="'richtext-property-' . $key" />
@elseif($property['type'] === 'simpletext')
    <livewire:block-properties.simpletext-property :property-name="$property['name']" :property-label="$property['label']"
        :current-value="$properties[$property['name']] ?? ''" :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null"
        :key="'simpletext-property-' . $key" />
@elseif($property['type'] === 'flexible-size')
    <livewire:block-properties.flexible-size-property :property="$property" :value="$properties[$property['name']] ?? ''"
        :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null" :key="'flexible-size-property-' . $key" />
@elseif($property['type'] === 'responsive-spacing')
    @php
        $currentValues = [];
        foreach ($property['fields'] as $deviceKey => $directions) {
            foreach ($directions as $directionKey => $fieldName) {
                $currentValues[$deviceKey][$directionKey] =
                    $properties[$fieldName] ?? ($property['values'][$deviceKey][$directionKey] ?? null);
            }
        }
    @endphp
    <livewire:block-properties.responsive-spacing-property :property="$property" :values="$currentValues"
        :row-id="$rowId" :block-id="$blockId" :update-target="$updateTarget ?? null" :key="'responsive-spacing-property-' . $key" />
@elseif($property['type'] === 'custom')
    @php
        $componentName = $property['component'] ?? null;
    @endphp
    @if($componentName)
        {{-- Host-supplied widgets do not know about $updateTarget, so they keep dispatching
             globally; the live edit sheet listens for that too. --}}
        <livewire:is :component="$componentName"
            :property-name="$property['name']"
            :property-label="$property['label']"
            :property-config="$property['config'] ?? []"
            :current-value="$properties[$property['name']] ?? $property['defaultValue'] ?? null"
            :row-id="$rowId"
            :block-id="$blockId"
            :key="'custom-property-' . $key" />
    @endif
@else
    <div>
        <label class="flex justify-between text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
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
