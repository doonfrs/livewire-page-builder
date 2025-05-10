<div>
    <div class="{{ $cssClasses }} group" @if (!empty($inlineStyles)) style="{{ $inlineStyles }}" @endif>
        @foreach ($blocks as $blockId => $block)
            @livewire(
                'builder-block',
                [
                    'blockAlias' => $block['alias'],
                    'blockId' => $blockId,
                    'properties' => $block['properties'] ?? [],
                    'viewMode' => true,
                ],
                key($blockId)
            )
        @endforeach
    </div>
</div>
