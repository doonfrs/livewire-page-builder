<div style="font-size:0">
    @foreach ($rows as $rowId => $row)
    <livewire:row-block :blocks="$row['blocks']" :edit-mode="false" :rowId="$rowId" :properties="$row['properties']"
        :key="$rowId" />
    @endforeach
</div>