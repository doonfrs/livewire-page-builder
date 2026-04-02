<div class="{{ $row['cssClasses'] }} group" style="{{ $row['inlineStyles'] }}" {!! $row['dataAttributes'] ?? '' !!}>
    <div class="{{ $row['rowCssClasses'] }}">
        @foreach ($row['blocks'] as $blockId => $block)
            @php
                $componentExists =
                    $block['component_exists'] ??
                    app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->isBlockAliasRegistered(
                        $block['alias'] ?? '',
                    );
            @endphp

            @if (!$componentExists)
                @continue
            @endif

            <div class="{{ $block['cssClasses'] }}" style="{{ $block['inlineStyles'] }}" {!! $block['dataAttributes'] ?? '' !!}>
                @if ($block['alias'] == 'builder-page-block')
                    <div style="font-size:0">
                        @foreach ($block['rows'] ?? [] as $rowId => $row)
                            <x-page-builder::row-view :row="$row" />
                        @endforeach
                    </div>
                @elseif (str_contains($block['alias'], 'row-block') && isset($block['blocks']))
                    <div style="font-size:initial" class="h-full w-full">
                        @livewire(
                            $block['alias'],
                            [
                                'blocks' => $block['blocks'],
                                'rowId' => $blockId,
                                'properties' => $block['properties'],
                                'isNested' => true,
                                'editMode' => false,
                            ],
                            key($blockId)
                        )
                    </div>
                @else
                    @php
                        $hasFontSize = !empty($block['properties']['mobileFontSize'] ?? null)
                            || !empty($block['properties']['tabletFontSize'] ?? null)
                            || !empty($block['properties']['desktopFontSize'] ?? null);
                    @endphp
                    <div @if(!$hasFontSize) style="font-size:initial" @endif class="h-full w-full content-center">
                        @php
                            $lazyMode = $block['properties']['lazyLoad'] ?? 'disabled';
                            $isEditMode = $block['properties']['editMode'] ?? false;
                            $lazyValue = (!$isEditMode && ($lazyMode === 'on' || $lazyMode === true)) ? true : ((!$isEditMode && $lazyMode === 'on-load') ? 'on-load' : null);
                        @endphp
                        @livewire($block['alias'], array_merge($block['properties'], $lazyValue !== null ? ['lazy' => $lazyValue] : []), key($blockId))
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
