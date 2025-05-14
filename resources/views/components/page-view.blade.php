@props(['pageKey'])
@php
$page = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderRender::class)->parsePage($pageKey);
@endphp
<div class="@container" style="font-size:0">
    @foreach ($page['rows'] as $rowId => $row)
    <x-page-builder::row-view :row="$row" />
    @endforeach
</div>