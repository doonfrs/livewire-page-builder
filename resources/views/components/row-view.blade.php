<div class="{{ $row['cssClasses'] }} group inline-block" style="font-size:initial"
    @if (!empty($row['inlineStyles'])) style="{{ $row['inlineStyles'] }}" @endif>
    <div class="{{ $row['rowCssClasses'] }}">
        @foreach ($row['blocks'] as $blockId => $block)
            <div class="{{ $block['cssClasses'] }}" style="{{ $block['inlineStyles'] }}">
                @if ($block['alias'] == 'builder-page-block')
                    <div style="font-size:0">
                        @foreach ($block['rows'] as $rowId => $row)
                            <x-page-builder::row-view :row="$row" />
                        @endforeach
                    </div>
                @else
                    @livewire($block['alias'], $block['properties'], key($blockId))
                @endif
            </div>
        @endforeach
    </div>
</div>
