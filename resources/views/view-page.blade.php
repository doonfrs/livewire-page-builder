<x-app-layout>
    <main class="mx-auto @container" style="font-size:0">
        @foreach ($rows as $rowId => $row)
            <x-page-builder::row-view :row="$row" />
        @endforeach
    </main>
</x-app-layout>
