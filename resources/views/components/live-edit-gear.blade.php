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
        {{-- z-[60] beats the z-50 a block can set on itself; font-size:initial escapes the wrapper's font-size:0 --}}
        class="btn btn-circle btn-xs align-middle border-base-300 bg-base-100 text-base-content/40 shadow-sm hover:text-base-content hover:bg-base-200 absolute top-1 start-1 z-[60]"
        @if ($pbLiveEditToggle) x-cloak x-show="{{ $pbLiveEditToggle }}" @endif
        x-on:click.stop.prevent="Livewire.dispatch('pb-live-edit', { ctx: @js($ctx) })">
        <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
    </button>
@endif
