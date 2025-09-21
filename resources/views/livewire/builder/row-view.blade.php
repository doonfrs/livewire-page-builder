<div class="{{ $cssClasses }}" style="{{ $inlineStyles }} font-size:initial">
    <div class="row-blocks {{ $rowCssClasses }}">
        @foreach ($blocks as $blockId => $block)
            @if (str_contains($block['alias'], 'row-block') && isset($block['blocks']))
                {{-- For nested row blocks, render them directly with their properties --}}
                @livewire(
                    $block['alias'],
                    [
                        'blocks' => $block['blocks'],
                        'rowId' => $blockId,
                        'properties' => $block['properties'],
                        'editMode' => false,
                    ],
                    key($blockId)
                )
            @else
                {{-- For regular blocks, render them directly without builder wrapper --}}
                <div class="{{ app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->getCssClassesFromProperties($block['properties'] ?? [], false) }}"
                     style="{{ app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->getInlineStylesFromProperties($block['properties'] ?? []) }}">
                    @livewire($block['alias'], $block['properties'] ?? [], key($blockId))
                </div>
            @endif
        @endforeach
    </div>
</div>
