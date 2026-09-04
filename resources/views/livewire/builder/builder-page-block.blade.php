<div style="font-size:0">
    @foreach ($rows ?? [] as $rowId => $row)
        {{-- Render row directly without Livewire component to avoid component tracking issues during replacement --}}
        @php
            $rowProperties = $row['properties'] ?? [];
            $rowBlocks = $row['blocks'] ?? [];
            $rowCssClasses = app(
                \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
            )->getRowCssClassesFromProperties($rowProperties);
            $cssClasses = app(
                \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
            )->getCssClassesFromProperties($rowProperties);
            $inlineStyles = app(
                \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
            )->getInlineStylesFromProperties($rowProperties);
            $dataAttributes = app(
                \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
            )->getDataAttributesFromProperties($rowProperties);
        @endphp

        <div class="{{ $cssClasses }}" style="{{ $inlineStyles }} font-size:initial" {!! $dataAttributes !!}>
            <div class="row-blocks {{ $rowCssClasses }}">
                @foreach ($rowBlocks as $blockId => $block)
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
                        <div
                            class="rounded-md border border-dashed border-amber-400 bg-amber-50 p-4 text-sm text-amber-700">
                            <p class="font-semibold">{{ __('Missing block component') }}</p>
                            <p class="mt-1">{{ __(':block is no longer available.', ['block' => $readableAlias]) }}</p>
                        </div>
                        @continue
                    @endif

                    @if (str_contains($block['alias'], 'row-block') && isset($block['blocks']))
                        {{-- For nested row blocks, render as Livewire component --}}
                        @livewire(
                            $block['alias'],
                            [
                                'blocks' => $block['blocks'],
                                'rowId' => $blockId,
                                'properties' => $block['properties'],
                                'isNested' => true,
                                'editMode' => false,
                                'liveEditContext' => $liveEditContexts[$rowId][$blockId] ?? null,
                            ],
                            key('pb-nested-' . $blockPageName . '-' . $blockId)
                        )
                    @else
                        {{-- For regular blocks, render them directly --}}
                        @php
                            $liveEditGear = $liveEditGears[$rowId][$blockId] ?? null;
                            $blockCssClasses = app(
                                \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
                            )->getCssClassesFromProperties($block['properties'] ?? [], false);
                            // A block whose content sits well inside this wrapper draws the
                            // gear itself, next to what it edits, and gets the context instead.
                            $ownGear = $liveEditGear && app(
                                \Trinavo\LivewirePageBuilder\Services\PageBuilderService::class,
                            )->blockDrawsOwnLiveEditGear($block['alias'] ?? null);
                            if ($liveEditGear && !$ownGear && !preg_match('/\b(relative|absolute|fixed|sticky)\b/', $blockCssClasses)) {
                                $blockCssClasses .= ' relative';
                            }
                            $liveEditId = $liveEditGear ? \Trinavo\LivewirePageBuilder\Http\Livewire\LiveEdit::domId($liveEditGear) : null;
                        @endphp
                        <div @if ($liveEditId) id="{{ $liveEditId }}" @endif class="{{ $blockCssClasses }}"
                            style="{{ app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->getInlineStylesFromProperties($block['properties'] ?? []) }}"
                            {!! app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class)->getDataAttributesFromProperties(
                                $block['properties'] ?? [],
                            ) !!}>
                            @unless ($ownGear)
                                <x-page-builder::live-edit-gear :ctx="$liveEditGear" />
                            @endunless
                            @livewire($block['alias'], array_merge($block['properties'] ?? [], $ownGear ? ['liveEditContext' => $liveEditGear] : []), key('pb-block-' . $blockPageName . '-' . $blockId))
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
