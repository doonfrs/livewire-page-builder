<x-page-builder::live-edit-mount />
<div class="{{ $row['cssClasses'] }} group" style="{{ $row['inlineStyles'] }}" {!! $row['dataAttributes'] ?? '' !!}>
    <div class="{{ $row['rowCssClasses'] }}">
        @foreach ($row['blocks'] as $blockId => $block)
            @php
                $componentExists =
                    $block['component_exists'] ??
                    app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->isBlockAliasRegistered(
                        $block['alias'] ?? '',
                    );
                $liveEditCtx = ($block['liveEditable'] ?? false) ? $block['liveEditContext'] ?? null : null;
                // The gear is absolutely positioned inside this wrapper, so the wrapper has to
                // be a containing block. absolute/fixed/sticky already are; adding `relative`
                // on top of them would just fight for source order.
                $blockCssClasses = $block['cssClasses'] ?? '';
                if ($liveEditCtx && !preg_match('/\b(relative|absolute|fixed|sticky)\b/', $blockCssClasses)) {
                    $blockCssClasses .= ' relative';
                }
                // Anchor the browser needs to find this exact block when the sheet previews
                // a change into it. Derived from the context, not the block id: block ids
                // repeat across pages, and one page can be embedded more than once.
                $liveEditId = $liveEditCtx ? \Trinavo\LivewirePageBuilder\Http\Livewire\LiveEdit::domId($liveEditCtx) : null;
            @endphp

            @if (!$componentExists)
                @continue
            @endif

            <div @if ($liveEditId) id="{{ $liveEditId }}" @endif class="{{ $blockCssClasses }}"
                style="{{ $block['inlineStyles'] }}" {!! $block['dataAttributes'] ?? '' !!}>
                <x-page-builder::live-edit-gear :ctx="$liveEditCtx" />
                @if ($block['alias'] == 'builder-page-block')
                    {{-- Distinct loop variable: reusing $row here would shadow the row we are rendering. --}}
                    <div style="font-size:0">
                        @foreach ($block['rows'] ?? [] as $embeddedRow)
                            <x-page-builder::row-view :row="$embeddedRow" />
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
                                'liveEditContext' => $block['liveEditContext'] ?? null,
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
