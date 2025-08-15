<div class="{{ $row['cssClasses'] }} group" style="{{ $row['inlineStyles'] }}">
    <div class="{{ $row['rowCssClasses'] }}">
        @foreach ($row['blocks'] as $blockId => $block)
            <div class="{{ $block['cssClasses'] }}" style="{{ $block['inlineStyles'] }}">
                @if ($block['alias'] == 'builder-page-block')
                    <div style="font-size:0">
                        @foreach ($block['rows'] ?? [] as $rowId => $row)
                            <x-page-builder::row-view :row="$row" />
                        @endforeach
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
