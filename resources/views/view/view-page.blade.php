<x-app-layout>
    <main class="mx-auto @container">
        @foreach ($rows as $rowId => $row)
            <div id="page-builder-row-{{ $rowId }}">
                <livewire:row-block :blocks="$row['blocks']" :rowId="$rowId" :properties="$row['properties']"
                    :key="$rowId" />
            </div>
        @endforeach
    </main>
    @include('page-builder::shared.safe-classes')
</x-app-layout>
