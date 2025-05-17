<div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900" x-data="{ showPagesModal: false, deviceMode: 'desktop', loading: true }"
    x-on:row-added.window="setTimeout(() => { 
            const el = document.getElementById('row-' + $event.detail.rowId); 
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); 
            $dispatch('row-selected', { rowId: $event.detail.rowId, properties: $event.detail.properties });
    }, 200);"
    x-on:block-added.window="setTimeout(() => { 
            const el = document.getElementById('block-' + $event.detail.blockId); 
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); 
    }, 200);"
    x-on:select-block.window="
        setTimeout(() => {
            const el = document.getElementById('block-' + $event.detail.blockId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    "
    x-on:select-row.window="
        setTimeout(() => {
            const el = document.getElementById('row-' + $event.detail.rowId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    "
    x-init="() => {
        // Wait for DOM content to be fully loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Wait for all components and images to be loaded
            window.addEventListener('load', () => {
                setTimeout(() => {
                    loading = false;
                }, );
            });
    
            // If window.load takes too long, still hide the loader after a maximum time
            setTimeout(() => {
                loading = false;
            }, 2000);
        });
    
        // Also listen for Livewire page loads
        window.addEventListener('livewire:navigating', () => {
            loading = true;
        });
    
        window.addEventListener('livewire:navigated', () => {
            setTimeout(() => {
                loading = false;
            }, 300);
        });
    }" wire:loading.class="cursor-wait">

    <!-- Loading Overlay -->
    <div x-show="loading" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-100 flex items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="text-center">
            <svg class="w-16 h-16 mx-auto text-pink-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="mt-4 text-lg font-medium text-gray-800 dark:text-gray-200"></p>
        </div>
    </div>

    <!-- Livewire Operations Loading Indicator -->
    <div wire:loading.delay wire:target="addRow, closeBlockModal, addBlockToModalRow"
        class="fixed top-0 right-0 z-50 m-4 p-2 bg-pink-100 dark:bg-pink-900 rounded-lg shadow-lg flex items-center text-pink-600 dark:text-pink-300 text-sm font-medium">
        <svg class="w-5 h-5 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
    </div>

    <!-- Header Toolbar -->
    <div
        class="flex items-center justify-between bg-gray-200 dark:bg-gray-800 shadow-md p-3 text-gray-900 dark:text-gray-100 z-30">
        <div class="flex gap-2">
            <!-- Add Button -->
            <button
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium"
                wire:click="addRow" title="{{ __('Add Row') }}">
                <x-heroicon-o-plus class="w-5 h-5" />
            </button>
            <!-- List Blocks Button -->
            <button
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium"
                wire:click="$dispatch('openPageBlocksModal')" title="{{ __('List Blocks') }}">
                <x-heroicon-o-list-bullet class="w-5 h-5" />
            </button>
            <!-- Pages Button -->
            <button x-on:click="showPagesModal = true"
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-200 transition-all duration-150 text-sm font-medium"
                title="{{ __('Open Pages') }}">
                <x-heroicon-o-document-text class="w-5 h-5" />
            </button>
            <!-- Preview Button -->
            <a :href="'/page-builder/page/view/' + @js($pageKey ?? '') + (@js($pageTheme ?? '') ? '/' +
                @js($pageTheme ?? '') : '')"
                target="_blank"
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-200 transition-all duration-150 text-sm font-medium"
                title="{{ __('Preview Page') }}">
                <x-heroicon-o-eye class="w-5 h-5" />
            </a>
            <!-- Save Button with animation placeholder -->
            <button
                class="flex items-center w-14 gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium relative"
                wire:click="$dispatch('save-page')" x-data="{ saved: false }"
                x-on:click="saved = true; setTimeout(() => saved = false, 1200);" title="{{ __('Save Page') }}">
                <span x-show="!saved" class="flex items-center gap-1 justify-center">
                    <x-heroicon-o-check class="w-5 h-5" />
                </span>
                <span x-show="saved" x-transition class="absolute inset-0 flex items-center justify-center">
                    <x-heroicon-s-check class="w-5 h-5 text-green-500 animate-bounce" />
                </span>
            </button>
        </div>
        <div class="flex items-center space-x-4">
            <div
                class="flex gap-0 border border-gray-300 dark:border-gray-700 rounded-md overflow-hidden bg-white dark:bg-gray-900">
                <button :class="deviceMode === 'mobile' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''"
                    x-on:click="deviceMode = 'mobile'"
                    class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0"
                    title="Mobile View">
                    <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
                </button>
                <button :class="deviceMode === 'tablet' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''"
                    x-on:click="deviceMode = 'tablet'"
                    class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0"
                    title="Tablet View">
                    <x-heroicon-o-device-tablet class="w-5 h-5" />
                </button>
                <button :class="deviceMode === 'desktop' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''"
                    x-on:click="deviceMode = 'desktop'"
                    class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150"
                    title="Desktop View">
                    <x-heroicon-o-computer-desktop class="w-5 h-5" />
                </button>
            </div>
            <!-- Language Switcher -->
            <livewire:language-switcher />
        </div>
    </div>

    <!-- Modal for Adding Block -->
    @if ($showBlockModal)
        @include('page-builder::livewire.builder.partials.blocks-modal', ['allBlocks' => $formattedBlocks])
    @endif

    <!-- Modal for Page Blocks -->
    @if ($showPageBlocksModal)
        @include('page-builder::livewire.builder.partials.page-blocks-modal', [
            'allPageBlocks' => $allPageBlocks,
        ])
    @endif

    <!-- Pages Modal -->
    @include('page-builder::livewire.builder.partials.pages-modal')

    <!-- Main Content and Properties Panel -->
    <div class="flex flex-1 min-h-0">

        <!-- Properties Panel (Fixed/Sticky) -->
        <aside
            class="hidden lg:block w-[20%] h-[calc(100vh-56px)] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-lg overflow-y-auto">
            @livewire('block-properties')
        </aside>

        <!-- Main Section (Scrollable) -->
        <main class="flex-1 pt-10 pb-50 pr-0 bg-gray-50 dark:bg-gray-900 overflow-auto min-h-0 w-[80%]">
            <div class="mx-auto @container"
                :class="{
                    'w-[375px]': deviceMode === 'mobile',
                    'w-[768px]': deviceMode === 'tablet',
                    'w-full': deviceMode === 'desktop',
                }"
                style="font-size:0">
                @foreach ($rows as $rowId => $row)
                    <livewire:row-block :edit-mode="true" :blocks="$row['blocks']" :rowId="$rowId" :properties="$row['properties']"
                        :key="$rowId" />
                @endforeach
            </div>
        </main>
    </div>
</div>
