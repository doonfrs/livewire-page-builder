<aside class="bg-white p-4 border-r border-gray-300 shadow-md overflow-y-auto h-lvh" @widget-selected="$refresh">
    <h2 class="text-lg font-semibold mb-4">Block Title {{$selectedBlockId}} {{$selectedRowId}}</h2>
    <div class="space-y-3">
        <div>
            <label class="block text-sm font-medium text-gray-700">Property 1</label>
            <input type="text" class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-gray-300">
        </div>
    </div>
</aside>