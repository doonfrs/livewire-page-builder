<div class="grid grid-cols-12 mb-2 ">
    <div class="{{ $cssClasses }} group">
        <div class="relative transition-all duration-300 ease-in-out">
            <div class="grid grid-cols-12">
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
    </div>
</div>
