{{--
    Mounts the single live edit property sheet for the request.

    Lives in row-view rather than in view-page/page-view because hosts publish and
    override view-page.blade.php, while every public render path funnels through
    <x-page-builder::row-view />. The @once id is explicit: a bare @once compiles a
    fresh uuid per call site, so two call sites would each mount their own sheet.
--}}
@once('page-builder-live-edit')
    @if (app(\Trinavo\LivewirePageBuilder\Services\PageBuilderUIService::class)->isLiveEditEnabled())
        @livewire('page-builder-live-edit')
    @endif
@endonce
