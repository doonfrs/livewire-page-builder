@php
    $cssClasses = $row['cssClasses'];
    $inlineStyles = $row['inlineStyles'];
    $blocks = $row['blocks'];
@endphp
<div class="{{ $cssClasses }} group" @if (!empty($inlineStyles)) style="{{ $inlineStyles }}" @endif>
    @foreach ($blocks as $blockId => $block)
        <div class="{{ $cssClasses }}" style="{{ $inlineStyles }}">
            @if ($block['alias'] == 'builder-page-block')
                @foreach ($block['rows'] as $rowId => $row)
                    <x-page-builder::row-view :row="$row" />
                @endforeach
            @else
                @livewire($block['alias'], $block['properties'], key($blockId))
            @endif
        </div>
    @endforeach
</div>
