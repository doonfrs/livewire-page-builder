<div class="flex flex-col flex-1 min-h-0 h-full">
    <!-- Header (fixed; title + block name on one line; only the content below scrolls) -->
    <div
        class="shrink-0 bg-gradient-to-r from-gray-800 to-gray-700 text-white px-4 py-2 border-b border-gray-700 shadow-md flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 min-w-0">
            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 shrink-0" />
            <h2 class="text-base font-medium shrink-0">{{ __('Properties') }}</h2>
            <span class="text-xs font-mono bg-gray-900/50 rounded px-2 py-0.5 truncate min-w-0">
                {{ $blockLabel ?? __('No block selected') }}
            </span>
        </div>
        <button type="button" class="shrink-0 p-1 rounded hover:bg-white/10 transition-colors"
            x-on:click="$dispatch('close-properties-panel')" title="{{ __('Close') }}">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
    </div>

    <!-- Scrollable content (only this area scrolls) -->
    <div class="flex-1 min-h-0 overflow-y-auto pb-compact">
        <!-- Empty State -->
    @if ($componentMissing)
        @php
            $alias = $missingBlockAlias ?? __('unknown');
            $readableAlias = \Illuminate\Support\Str::of($alias)
                ->after('page-builder-')
                ->replace('-', ' ')
                ->headline();
        @endphp
        <div class="flex flex-col items-center justify-center h-64 text-center p-6 text-amber-700">
            <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-amber-400 mb-3" />
            <div class="text-lg font-semibold">{{ __('Missing block component') }}</div>
            <div class="text-sm mt-1">
                {{ __(':block (:alias) is no longer available. Remove or replace this block to keep the page working.', [
                    'block' => $readableAlias,
                    'alias' => $alias,
                ]) }}
            </div>
        </div>
    @elseif (empty($blockProperties) || empty($properties))
        <div class="flex flex-col items-center justify-center h-64 text-center p-6">
            <x-heroicon-o-cube class="w-12 h-12 text-gray-300 mb-3 dark:text-gray-600" />
            <div class="text-gray-500 font-medium dark:text-gray-400">{{ __('No properties available') }}</div>
            <div class="text-gray-400 text-sm mt-1 dark:text-gray-500">
                {{ __('Select a block to view and edit its properties') }}</div>
        </div>
    @else
        <!-- Properties Groups -->
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <!-- Property Groups -->
            @foreach ($propertyGroups as $groupName => $group)
                <div class="px-3 py-4 @if ($loop->even) bg-gray-50 dark:bg-gray-800/50 @endif">
                    <div
                        class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 flex items-center dark:text-gray-400">
                        <x-dynamic-component :component="$group['icon']" class="w-4 h-4 mr-1" />
                        {{ $group['label'] }}
                    </div>

                    <div class="space-y-3 {{ $group['columns'] > 1 ? 'grid grid-cols-' . $group['columns'] . ' gap-2 space-y-0' : '' }}"
                        wire:key="group-{{ $blockId }}-{{ $groupName }}">
                        @foreach ($group['properties'] as $property)
                            @php
                                $key = ($rowId ?? '') . '-' . ($blockId ?? '') . '-' . $property['name'];
                            @endphp
                            <div wire:key="property-{{ $key }}" class="group">
                                @include('page-builder::livewire.builder.partials.property-field', [
                                    'property' => $property,
                                    'properties' => $properties,
                                    'rowId' => $rowId,
                                    'blockId' => $blockId,
                                    'key' => $key,
                                    'updateTarget' => null,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    </div>

    {{-- Compact panel: smaller fonts across the main view and all property sub-components to gain space --}}
    <style>
        .pb-compact label,
        .pb-compact input,
        .pb-compact select,
        .pb-compact textarea {
            font-size: 0.75rem;
        }
    </style>
</div>
