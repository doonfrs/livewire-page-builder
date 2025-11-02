<div class="{{ $cssClasses }}" style="{{ $inlineStyles }} font-size:initial" {!! $dataAttributes !!}>
    <div class="row-blocks {{ $rowCssClasses }}">
        @foreach ($blocks as $blockId => $block)
            @php
                $alias = $block['alias'] ?? 'unknown';
                $componentExists = app(
                    \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
                )->isBlockAliasRegistered($alias);
            @endphp

            @if (!$componentExists)
                @php
                    $readableAlias = \Illuminate\Support\Str::of($alias)
                        ->after('page-builder-')
                        ->replace('-', ' ')
                        ->headline();
                @endphp

                <div class="rounded-md border border-dashed border-amber-400 bg-amber-50 p-4 text-sm text-amber-700">
                    <p class="font-semibold">{{ __('Missing block component') }}</p>
                    <p class="mt-1">
                        {{ __(':block is no longer available. ', [
                            'block' => $readableAlias,
                        ]) }}
                    </p>
                </div>

                @continue
            @endif

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
                    style="{{ app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->getInlineStylesFromProperties($block['properties'] ?? []) }}"
                    {!! app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->getDataAttributesFromProperties($block['properties'] ?? []) !!}>
                    @livewire($block['alias'], $block['properties'] ?? [], key($blockId))
                </div>
            @endif
        @endforeach
    </div>
</div>
