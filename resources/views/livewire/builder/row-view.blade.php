<div class="{{ $cssClasses }} inline-block" style="{{ $inlineStyles }}" style="font-size:initial">
    <div class="row-blocks {{ count($blocks) == 0 ? 'pt-4 pb-4' : '' }} {{ $flex ? "flex flex-{$flex}" : '' }}">
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
