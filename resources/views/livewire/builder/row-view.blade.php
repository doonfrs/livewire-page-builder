<div class="{{ $cssClasses }}" style="{{ $inlineStyles }}" style="font-size:initial">
    <div class="row-blocks {{ $flex ? "flex flex-{$flex}" : '' }}">
        @foreach ($blocks as $blockId => $block)
            @livewire(
                'builder-block',
                [
                    'blockAlias' => $block['alias'],
                    'blockId' => $blockId,
                    'rowId' => $rowId,
                    'properties' => $block['properties'] ?? [],
                    'editMode' => true,
                ],
                key($blockId)
            )
        @endforeach
    </div>
</div>
