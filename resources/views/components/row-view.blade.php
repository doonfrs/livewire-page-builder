<div class="{{ $row['cssClasses'] }} group" style="{{ $row['inlineStyles'] }}">
    <div class="{{ $row['rowCssClasses'] }}">
        @foreach ($row['blocks'] as $blockId => $block)
            @php
                $componentExists = $block['component_exists'] ?? app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)
                    ->isBlockAliasRegistered($block['alias'] ?? '');
            @endphp

            @if (! $componentExists)
                @continue
            @endif

            <div class="{{ $block['cssClasses'] }}" style="{{ $block['inlineStyles'] }}">
                @if ($block['alias'] == 'builder-page-block')
                    <div style="font-size:0">
                        @foreach ($block['rows'] ?? [] as $rowId => $row)
                            <x-page-builder::row-view :row="$row" />
                        @endforeach
                    </div>
                @elseif (str_contains($block['alias'], 'row-block') && isset($block['blocks']))
                    <div style="font-size:initial">
                        @livewire($block['alias'], [
                            'blocks' => $block['blocks'],
                            'rowId' => $blockId,
                            'properties' => $block['properties'],
                            'editMode' => false,
                        ], key($blockId))
                    </div>
                @else
                    <div style="font-size:initial">
                        @livewire($block['alias'], $block['properties'], key($blockId))
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
