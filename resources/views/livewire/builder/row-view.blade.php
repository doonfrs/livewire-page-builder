<div class="{{ $cssClasses }}" style="{{ $inlineStyles }} font-size:initial">
    <div class="row-blocks {{ $rowCssClasses }}">
        @foreach ($blocks as $blockId => $block)
            @livewire(
                'builder-block',
                [
                    'blockAlias' => $block['alias'],
                    'blockId' => $blockId,
                    'rowId' => $rowId,
                    'properties' => $block['properties'] ?? [],
                    'blocks' => $block['blocks'] ?? [], // Pass nested blocks for RowBlock
                    'editMode' => $properties['editMode'] ?? false,
                ],
                key($blockId)
            )
        @endforeach
    </div>
</div>
