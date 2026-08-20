{{--
    Live edit gear: opens the property sheet for one block on the public page.

    Rendered by the row/page-block views as the first child of a block's wrapper div,
    which is why the wrapper gets `relative` when it has no position class of its own.
    Deliberately outside the block's own @livewire() call so it is still visible while a
    lazy-loaded block is showing its placeholder.

    $ctx is the block's live edit context stamped by PageBuilderRender:
    ['page' => pageKey, 'theme' => themeId, 'path' => [rowId, blockId, ...]].
--}}
@props(['ctx' => null])

@if ($ctx)
    @php
        $pbLiveEditToggle = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderUIService::class)
            ->getLiveEditToggleExpression();
    @endphp

    <button type="button" title="{{ __('Edit block') }}" style="font-size:initial"
        {{-- z-[60] beats the z-50 a block can set on itself; font-size:initial escapes the wrapper's font-size:0.
             Anchored physically left rather than `start`, so the corner it occupies does not move between
             LTR and RTL - a block only has to keep one corner clear instead of both. --}}
        class="btn btn-circle btn-xs align-middle border-base-300 bg-base-100 text-base-content/40 shadow-sm hover:text-base-content hover:bg-base-200 absolute top-1 left-1 z-[60]"
        {{-- x-data makes the button an Alpine root. It lives in the row wrapper, outside every
             component's own x-data, and an x-cloak Alpine never processes is a permanently
             invisible button. --}}
        @if ($pbLiveEditToggle) x-data x-cloak x-show="{{ $pbLiveEditToggle }}" @endif
        x-on:click.stop.prevent="Livewire.dispatch('pb-live-edit', { ctx: @js($ctx) })">
        {{-- A pencil rather than a cog: this edits the block, and hosts usually have settings
             cogs of their own sitting on the same page. Solid, not outline: at 16px in a btn-xs
             the outline variants are thin enough to read as an empty button. --}}
        <x-heroicon-s-pencil class="h-4 w-4" />
    </button>
@endif
