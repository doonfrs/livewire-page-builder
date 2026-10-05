{{--
    Mounts the single live edit property sheet for the request.

    Lives in row-view rather than in view-page/page-view because hosts publish and
    override view-page.blade.php, while every public render path funnels through
    <x-page-builder::row-view />. The @once id is explicit: a bare @once compiles a
    fresh uuid per call site, so two call sites would each mount their own sheet.
--}}
@once('page-builder-live-edit')
    @if (app(\Trinavo\LivewirePageBuilder\Services\PageBuilderUIService::class)->isLiveEditEnabled())
        {{-- Hides the gears of a block that rendered nothing (Block::hasVisibleContent(),
             stamped as data-pb-hidden on its root by LiveEditVisibilityHook).
             First selector: the wrapper's gear, whose next sibling is the block root, or the
             div around it in row-view. Scoped to "wrapper" so a host control that merely sits
             before some other empty block is not caught. Second: a gear inside the block - one
             it draws itself, or a host control marked data-pb-edit-gear.
             CSS rather than a server check because the wrapper renders before the block, and
             :has() follows the root through every Livewire morph. Plain CSS so it does not
             depend on the host's Tailwind build. --}}
        <style>
            [data-pb-edit-gear="wrapper"]:has(+ [data-pb-hidden], + * > [data-pb-hidden]),
            [data-pb-hidden] [data-pb-edit-gear] { display: none; }
        </style>
        @livewire('page-builder-live-edit')
    @endif
@endonce
