<div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900"
     x-data
     x-on:row-added.window="setTimeout(() => { 
            const el = document.getElementById('row-' + $event.detail.rowId); 
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); 
            $dispatch('row-selected', { rowId: $event.detail.rowId, properties: $event.detail.properties });
    }, 100);"
>
    <!-- Header Toolbar -->
    <div class="flex items-center justify-between bg-gray-200 dark:bg-gray-800 shadow-md p-3 text-gray-900 dark:text-gray-100 sticky top-0 z-30">
        <div class="flex gap-2">
            <!-- Add Button -->
            <button
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium"
                wire:click="addRow"
                title="Add Row"
            >
                <x-heroicon-o-plus class="w-5 h-5" />
            </button>
            <!-- Save Button with animation placeholder -->
            <button
                class="flex items-center w-14 gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium relative"
                wire:click="$dispatch('save-page')"
                x-data="{ saved: false }"
                x-on:click="saved = true; setTimeout(() => saved = false, 1200);"
                title="Save Page"
            >
                <span x-show="!saved" class="flex items-center gap-1 justify-center">
                    <x-heroicon-o-check class="w-5 h-5" />
                </span>
                <span x-show="saved" x-transition class="absolute inset-0 flex items-center justify-center">
                    <x-heroicon-s-check class="w-5 h-5 text-green-500 animate-bounce" />
                </span>
            </button>
        </div>
        <!-- Device Toggle Buttons -->
        <div class="flex gap-0 border border-gray-300 dark:border-gray-700 rounded-md overflow-hidden bg-white dark:bg-gray-900">
            <button class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0" title="Mobile View">
                <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
            </button>
            <button class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0" title="Tablet View">
                <x-heroicon-o-device-tablet class="w-5 h-5" />
            </button>
            <button class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150" title="Desktop View">
                <x-heroicon-o-computer-desktop class="w-5 h-5" />
            </button>
        </div>
        <div class="w-16"></div> <!-- Spacer for symmetry -->
    </div>

    <!-- Modal for Adding Block -->
    @if($showBlockModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative">
            <button wire:click="closeBlockModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-plus class="w-6 h-6 text-pink-500" />
                Add Block
            </h2>
            <input type="text" wire:model="blockFilter" placeholder="Search blocks..." class="w-full border rounded-lg px-4 py-2 mb-6 focus:ring-2 focus:ring-pink-200 focus:border-pink-400 transition dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @forelse($this->filteredBlocks as $block)
                <button
                    wire:click="addBlockToModalRow('{{ $block['alias'] }}')"
                    class="group bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 p-5 flex flex-col items-center text-center focus:outline-none focus:ring-2 focus:ring-pink-200">
                    <x-dynamic-component :component="$block['icon'] ?? 'heroicon-o-cube'" class="w-10 h-10 mb-3 text-pink-500 group-hover:text-pink-600 transition-colors" />
                    <div class="font-semibold text-gray-800 dark:text-gray-100 mb-1 text-base">
                        {{ $block['label'] }}
                    </div>
                </button>
                @empty
                <div class="col-span-2 text-gray-400 text-center py-8">No blocks found.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content and Properties Panel -->
    <div class="flex flex-1 min-h-0">
        <!-- Main Section (Scrollable) -->
        <main class="flex-1 pt-10 pb-50 p-6 pr-80 bg-gray-50 dark:bg-gray-900 overflow-auto min-h-0">
            @foreach($rows as $rowId=>$row)
            <div id="row-{{ $rowId }}">
                <livewire:row-block 
                :blocks="$row['blocks']"
                :rowId="$rowId"
                :properties="$row['properties']"
                :key="$rowId" />
            </div>
            @endforeach
        </main>

        <!-- Properties Panel (Fixed/Sticky) -->
        <aside class="hidden lg:block h-full fixed right-0 top-[56px] z-40 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-lg overflow-y-auto">
            @livewire('block-properties')
        </aside>
    </div>

    {{--
        Tailwind safelist:
        col-span-1 col-span-2 col-span-3 col-span-4 col-span-5 col-span-6 col-span-7 col-span-8 col-span-9 col-span-10 col-span-11 col-span-12
        md:col-span-1 md:col-span-2 md:col-span-3 md:col-span-4 md:col-span-5 md:col-span-6 md:col-span-7 md:col-span-8 md:col-span-9 md:col-span-10 md:col-span-11 md:col-span-12
        lg:col-span-1 lg:col-span-2 lg:col-span-3 lg:col-span-4 lg:col-span-5 lg:col-span-6 lg:col-span-7 lg:col-span-8 lg:col-span-9 lg:col-span-10 lg:col-span-11 lg:col-span-12
    --}}
</div>