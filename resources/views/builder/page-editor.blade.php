<div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900"
    x-data="{ showPagesModal: false, deviceMode: 'desktop', loading: true }"
    x-on:row-added.window="setTimeout(() => { 
            const el = document.getElementById('row-' + $event.detail.rowId); 
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); 
            $dispatch('row-selected', { rowId: $event.detail.rowId, properties: $event.detail.properties });
    }, 100);"
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
    }"
    wire:loading.class="cursor-wait">
    
    <!-- Loading Overlay -->
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-100 flex items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="text-center">
            <svg class="w-16 h-16 mx-auto text-pink-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-lg font-medium text-gray-800 dark:text-gray-200"></p>
        </div>
    </div>

    <!-- Livewire Operations Loading Indicator -->
    <div wire:loading.delay wire:target="addRow, closeBlockModal, addBlockToModalRow" 
         class="fixed top-0 right-0 z-50 m-4 p-2 bg-pink-100 dark:bg-pink-900 rounded-lg shadow-lg flex items-center text-pink-600 dark:text-pink-300 text-sm font-medium">
        <svg class="w-5 h-5 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Header Toolbar -->
    <div class="flex items-center justify-between bg-gray-200 dark:bg-gray-800 shadow-md p-3 text-gray-900 dark:text-gray-100 z-30">
        <div class="flex gap-2">
            <!-- Add Button -->
            <button
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium"
                wire:click="addRow"
                title="{{ __('Add Row') }}">
                <x-heroicon-o-plus class="w-5 h-5" />
            </button>
            <!-- Pages Button -->
            <button
                x-on:click="showPagesModal = true"
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-200 transition-all duration-150 text-sm font-medium"
                title="{{ __('Open Pages') }}">
                <x-heroicon-o-document-text class="w-5 h-5" />
                <span class="hidden sm:inline">{{ __('Pages') }}</span>
            </button>
            <!-- Preview Button -->
            <a
                :href="'/page-builder/page/view/' + @js($pageKey ?? '') + (@js($pageTheme ?? '') ? '/' + @js($pageTheme ?? '') : '')"
                target="_blank"
                class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-200 transition-all duration-150 text-sm font-medium"
                title="{{ __('Preview Page') }}">
                <x-heroicon-o-eye class="w-5 h-5" />
            </a>
            <!-- Save Button with animation placeholder -->
            <button
                class="flex items-center w-14 gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium relative"
                wire:click="$dispatch('save-page')"
                x-data="{ saved: false }"
                x-on:click="saved = true; setTimeout(() => saved = false, 1200);"
                title="{{ __('Save Page') }}">
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
            <button :class="deviceMode === 'mobile' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''" x-on:click="deviceMode = 'mobile'" class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0" title="Mobile View">
                <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
            </button>
            <button :class="deviceMode === 'tablet' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''" x-on:click="deviceMode = 'tablet'" class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0" title="Tablet View">
                <x-heroicon-o-device-tablet class="w-5 h-5" />
            </button>
            <button :class="deviceMode === 'desktop' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''" x-on:click="deviceMode = 'desktop'" class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150" title="Desktop View">
                <x-heroicon-o-computer-desktop class="w-5 h-5" />
            </button>
        </div>
        <div class="w-16"></div> <!-- Spacer for symmetry -->
    </div>

    <!-- Modal for Adding Block -->
    @if($showBlockModal)
    <div class="fixed inset-0 z-52 flex items-center justify-center bg-black/40">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative">
            <button wire:click="closeBlockModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-plus class="w-6 h-6 text-pink-500" />
                {{ __('Add Block') }}
            </h2>
            <input type="text" wire:model.live.debounce.500ms="blockFilter"
                placeholder="{{ __('Search blocks...') }}"
                class="w-full border rounded-lg px-4 py-2 mb-6 focus:ring-2 focus:ring-pink-200 focus:border-pink-400 transition dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 h-[40vh] overflow-auto">
                @forelse($this->filteredBlocks as $block)
                <button
                    wire:click="addBlockToModalRow('{{ $block['alias'] }}', '{{ $block['blockPageName'] ?? null }}')"
                    class="group h-40 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 p-5 flex flex-col items-center text-center focus:outline-none focus:ring-2 focus:ring-pink-200">
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

    <!-- Pages Modal -->
    <div x-show="showPagesModal" class="fixed inset-0 z-52 flex items-center justify-center bg-black/40" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative">
            <button @click="showPagesModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-document-text class="w-6 h-6 text-blue-500" />
                {{ __('Pages') }}
            </h2>
            <ul>
                @foreach(config('page-builder.pages', []) as $key => $page)
                @php
                $isAssoc = is_string($key) && is_array($page);
                if($isAssoc) {
                    $pageName = $key;
                    $pageLabel = null;
                    if(isset($page['label'])) {
                        $pageLabel = $page['label'];
                    }else{
                        $pageLabel = Str::headline($key);
                    }
                } else {
                    $pageName = $page;
                    $pageLabel = Str::headline($page);
                }
                $pageLabel = __($pageLabel);
                @endphp
                <li class="mb-2">
                    <a
                        href="{{ route('page-builder.page.edit', ['pageKey' => $pageName]) }}"
                        class="block px-4 py-2 rounded hover:bg-blue-100 dark:hover:bg-blue-900 transition text-gray-800 dark:text-gray-100">
                        <div class="flex items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <x-heroicon-o-document-text class="w-4 h-4 mr-2 me-2" />
                            {{ $pageLabel }}
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Main Content and Properties Panel -->
    <div class="flex flex-1 min-h-0">

        <!-- Properties Panel (Fixed/Sticky) -->
        <aside class="hidden lg:block w-[20%] h-[calc(100vh-56px)] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-lg overflow-y-auto">
            @livewire('block-properties')
        </aside>

        <!-- Main Section (Scrollable) -->
        <main class="flex-1 pt-10 pb-50 pr-0 bg-gray-50 dark:bg-gray-900 overflow-auto min-h-0 w-[80%]">
            <div
                class="mx-auto @container ps-4 pe-4"
                :class="{
                    'w-[375px]': deviceMode === 'mobile',
                    'w-[768px]': deviceMode === 'tablet',
                    'w-full': deviceMode === 'desktop',
                }">
                @foreach($rows as $rowId=>$row)
                <div id="row-{{ $rowId }}">
                    <livewire:row-block
                        :edit-mode="true"
                        :blocks="$row['blocks']"
                        :rowId="$rowId"
                        :properties="$row['properties']"
                        :key="$rowId" />
                </div>
                @endforeach
            </div>
        </main>
    </div>
    @include('page-builder::shared.safe-classes')
</div>
