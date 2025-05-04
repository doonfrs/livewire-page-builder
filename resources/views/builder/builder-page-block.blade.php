<div>
    @foreach ($rows as $rowId => $row)
    <div id="page-builder-row-{{ $rowId }}">
        <livewire:row-block :view-mode="true" :blocks="$row['blocks']" :rowId="$rowId" :properties="$row['properties']"
            :key="$rowId" />
    </div>
    @endforeach
</div>