<div>
    <div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900" x-data="{
        showPagesModal: false,
        showCopyFromModal: false,
        showCopyConfirmationModal: false,
        showImportConfirmModal: false,
        showImportFileModal: false,
        copySourcePageKey: null,
        copySourcePageLabel: null,
        deviceMode: 'desktop',
        canvasBgColor: '#f9fafb',
        loading: true,
        canPaste: false,
        pasteDataType: null,
        currentSelectedBlockId: null,
        currentSelectedRowId: null,
        checkClipboard: async function() {
            try {
                const text = await navigator.clipboard.readText();
                if (!text) {
                    this.canPaste = false;
                    this.pasteDataType = null;
                    return;
                }

                try {
                    // First try to parse as JSON
                    const data = JSON.parse(text);

                    // Check if it's our expected format with type property
                    if (data && typeof data === 'object') {
                        if (data.type === 'Block' || data.type === 'RowBlock') {
                            this.canPaste = true;
                            this.pasteDataType = data.type;
                            console.log('Valid clipboard data found:', data.type);
                            return;
                        }
                    }

                    this.canPaste = false;
                    this.pasteDataType = null;
                } catch (e) {
                    // Not valid JSON, that's fine
                    this.canPaste = false;
                    this.pasteDataType = null;
                }
            } catch (e) {
                // Clipboard access denied or other error
                console.error('Clipboard access error:', e);
                this.canPaste = false;
                this.pasteDataType = null;
            }
        }
    }"
        x-on:close-import-modal.window="showImportFileModal = false"
        x-on:row-added.window="setTimeout(() => {
            const el = document.getElementById('row-' + $event.detail.rowId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            $dispatch('row-selected', { rowId: $event.detail.rowId, properties: $event.detail.properties });
    }, 200);"
        x-on:block-added.window="
            console.log('➕ block-added event received', $event.detail);
            setTimeout(() => {
                const blockId = $event.detail.blockId;
                console.log('🔍 [block-added] Looking for element:', 'block-' + blockId);
                const el = document.getElementById('block-' + blockId);
                console.log('📍 [block-added] Element found:', el);
                if (el) {
                    console.log('📜 [block-added] Scrolling to block');
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    console.error('❌ [block-added] Element not found');
                }
            }, 500);
        "
        x-on:block-selected.window="
            currentSelectedBlockId = $event.detail.blockId;
            currentSelectedRowId = null;
            console.log('Block selected:', currentSelectedBlockId);
        "
        x-on:block-duplicated.window="
            const blockId = $event.detail.blockId;
            console.log('🔄 block-duplicated event received', {blockId});

            let retryCount = 0;
            const maxRetries = 10; // Max 2 seconds (10 * 200ms)

            // Wait for Livewire to finish morphing the DOM
            const checkAndScroll = () => {
                console.log('🔍 Looking for block element:', 'block-' + blockId, '(attempt', retryCount + 1, ')');
                const el = document.getElementById('block-' + blockId);
                if (el) {
                    console.log('✅ Element found! Scrolling to block:', blockId);
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Trigger selection after scroll
                    setTimeout(() => {
                        console.log('✅ Dispatching select-block event:', blockId);
                        Livewire.dispatch('select-block', { blockId: blockId });
                    }, 300);
                } else {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        console.log('⏳ Retry', retryCount, '/', maxRetries);
                        setTimeout(checkAndScroll, 200);
                    } else {
                        console.error('❌ Block element not found after', maxRetries, 'retries:', 'block-' + blockId);
                    }
                }
            };

            // Start checking after Livewire processes the response
            setTimeout(checkAndScroll, 300);
        "
        x-on:block-pasted.window="
            const blockId = $event.detail.blockId;
            console.log('📋 block-pasted event received', {blockId});

            let retryCount = 0;
            const maxRetries = 10; // Max 2 seconds (10 * 200ms)

            // Wait for Livewire to finish morphing the DOM
            const checkAndScroll = () => {
                console.log('🔍 Looking for pasted block element:', 'block-' + blockId, '(attempt', retryCount + 1, ')');
                const el = document.getElementById('block-' + blockId);
                if (el) {
                    console.log('✅ Element found! Scrolling to block:', blockId);
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Trigger selection after scroll
                    setTimeout(() => {
                        console.log('✅ Dispatching select-block event:', blockId);
                        Livewire.dispatch('select-block', { blockId: blockId });
                    }, 300);
                } else {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        console.log('⏳ Retry', retryCount, '/', maxRetries);
                        setTimeout(checkAndScroll, 200);
                    } else {
                        console.error('❌ Pasted block element not found after', maxRetries, 'retries:', 'block-' + blockId);
                    }
                }
            };

            // Start checking after Livewire processes the response
            setTimeout(checkAndScroll, 300);
        "
        x-on:row-duplicated.window="
            const rowId = $event.detail.rowId;
            console.log('🔄 row-duplicated event received', {rowId});

            let retryCount = 0;
            const maxRetries = 10; // Max 2 seconds (10 * 200ms)

            // Wait for Livewire to finish morphing the DOM
            const checkAndScroll = () => {
                console.log('🔍 Looking for row element:', 'row-' + rowId, 'or block-' + rowId, '(attempt', retryCount + 1, ')');

                // Try to find as a top-level row first
                let el = document.getElementById('row-' + rowId);

                // If not found, try as a nested row (which is wrapped in a block div)
                if (!el) {
                    console.log('🔍 Row not found, trying as block (nested row)');
                    el = document.getElementById('block-' + rowId);
                }

                if (el) {
                    console.log('✅ Element found! Scrolling to:', el.id);
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Trigger selection after scroll
                    setTimeout(() => {
                        console.log('✅ Dispatching select-row event:', rowId);
                        Livewire.dispatch('select-row', { rowId: rowId });
                    }, 300);
                } else {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        console.log('⏳ Retry', retryCount, '/', maxRetries);
                        setTimeout(checkAndScroll, 200);
                    } else {
                        console.error('❌ Row/Block element not found after', maxRetries, 'retries. Tried:', 'row-' + rowId, 'and block-' + rowId);
                    }
                }
            };

            // Start checking after Livewire processes the response
            setTimeout(checkAndScroll, 300);
        "
        x-on:row-pasted.window="
            const rowId = $event.detail.rowId;
            console.log('📋 row-pasted event received', {rowId});

            let retryCount = 0;
            const maxRetries = 10; // Max 2 seconds (10 * 200ms)

            // Wait for Livewire to finish morphing the DOM
            const checkAndScroll = () => {
                console.log('🔍 Looking for pasted row element:', 'row-' + rowId, 'or block-' + rowId, '(attempt', retryCount + 1, ')');

                // Try to find as a top-level row first
                let el = document.getElementById('row-' + rowId);

                // If not found, try as a nested row (which is wrapped in a block div)
                if (!el) {
                    console.log('🔍 Row not found, trying as block (nested row)');
                    el = document.getElementById('block-' + rowId);
                }

                if (el) {
                    console.log('✅ Element found! Scrolling to:', el.id);
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Trigger selection after scroll
                    setTimeout(() => {
                        console.log('✅ Dispatching select-row event:', rowId);
                        Livewire.dispatch('select-row', { rowId: rowId });
                    }, 300);
                } else {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        console.log('⏳ Retry', retryCount, '/', maxRetries);
                        setTimeout(checkAndScroll, 200);
                    } else {
                        console.error('❌ Pasted row/block element not found after', maxRetries, 'retries. Tried:', 'row-' + rowId, 'and block-' + rowId);
                    }
                }
            };

            // Start checking after Livewire processes the response
            setTimeout(checkAndScroll, 300);
        "
        x-on:row-selected.window="
            currentSelectedRowId = $event.detail.rowId;
            currentSelectedBlockId = null;
            console.log('Row selected:', currentSelectedRowId);
        "
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
        x-on:copy-to-clipboard.window="
        async (event) => {
            try {
                await navigator.clipboard.writeText(event.detail.data);
                // Success notification handled by component
            } catch (err) {
                console.error('Failed to copy: ', err);
                // Fallback method for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = event.detail.data;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback copy method failed:', err);
                }
                document.body.removeChild(textArea);
            }
        }
    "
        x-init="() => {
            // Don't check clipboard on load - only check on user interaction

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
                <x-heroicon-o-arrow-path class="w-16 h-16 mx-auto text-pink-500 animate-spin" />
                <p class="mt-4 text-lg font-medium text-gray-800 dark:text-gray-200"></p>
            </div>
        </div>

        <!-- Livewire Operations Loading Indicator -->
        <div wire:loading.delay wire:target="addRow, closeBlockModal, addBlockToModalRow"
            class="fixed top-0 right-0 z-50 m-4 p-2 bg-pink-100 dark:bg-pink-900 rounded-lg shadow-lg flex items-center text-pink-600 dark:text-pink-300 text-sm font-medium">
            <x-heroicon-o-arrow-path class="w-5 h-5 mr-2 animate-spin" />
        </div>

        <!-- Notifications -->
        @include('page-builder::livewire.builder.partials.notification')

        <!-- Header Toolbar -->
        <div
            class="flex items-center justify-between bg-gray-200 dark:bg-gray-800 shadow-md p-3 text-gray-900 dark:text-gray-100 z-30">

            <div class="flex gap-2">
                <!-- Theme Selector -->
                @if ($currentTheme)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium">
                            <x-heroicon-o-paint-brush class="w-5 h-5 text-pink-500" />
                            <span class="hidden sm:inline">{{ $currentTheme->name }}</span>
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </button>

                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-64 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 dark:divide-gray-700"
                            :class="document.documentElement.dir === 'rtl' ? 'right-0 origin-top-right' :
                                'left-0 origin-top-left'">

                            <div class="py-2">
                                <div
                                    class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    {{ __('Switch Theme') }}
                                </div>
                                @foreach ($availableThemes as $theme)
                                    <button wire:click="switchTheme({{ $theme['id'] }})" @click="open = false"
                                        class="flex items-center w-full px-4 py-2 text-left text-sm {{ $theme['id'] === $themeId ? 'bg-pink-50 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        @if ($theme['id'] === $themeId)
                                            <x-heroicon-o-check class="w-4 h-4 mr-2 text-pink-500" />
                                        @else
                                            <span class="w-4 h-4 mr-2"></span>
                                        @endif
                                        <div class="flex-1">
                                            <div class="font-medium">{{ $theme['name'] }}</div>
                                            @if ($theme['description'])
                                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $theme['description'] }}</div>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <div class="py-2">
                                <a href="{{ route('page-builder.themes') }}"
                                    class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-2 ml-2" />
                                    {{ __('Manage Themes') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <button wire:click="openThemeSelector"
                        class="flex items-center gap-2 px-4 py-2 border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-md hover:bg-yellow-100 dark:hover:bg-yellow-900/50 focus:ring-2 focus:ring-yellow-200 transition-all duration-150 text-sm font-medium">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        <span class="hidden sm:inline">{{ __('Select Theme') }}</span>
                    </button>
                @endif

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
                    <span class="hidden sm:inline">{{ $this->getCurrentPageLabel() }}</span>
                    @if ($this->isCurrentPageBlock())
                        <span
                            class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-700"
                            title="{{ __('This is a reusable page block') }}">
                            <x-heroicon-o-cube class="w-3 h-3 mr-1" />
                            {{ __('Block') }}
                        </span>
                    @endif
                </button>

                <!-- Copy From Button -->
                <button x-on:click="showCopyFromModal = true"
                    class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-green-200 transition-all duration-150 text-sm font-medium"
                    title="{{ __('Copy components from another page') }}">
                    <x-heroicon-o-document-duplicate class="w-5 h-5" />
                    <span class="hidden sm:inline">{{ __('Copy From') }}</span>
                </button>
                <!-- Preview Button -->
                <a :href="'/page-builder/page/view/' + @js($pageKey ?? '') + (@js($themeId ?? '') ? '/' +
                    @js($themeId ?? '') : '')"
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

                <!-- Canvas Background Color Picker -->
                <input type="color" id="canvas-bg-color" x-model="canvasBgColor"
                    class="w-10 h-10 rounded cursor-pointer border border-gray-300 dark:border-gray-600"
                    title="{{ __('Change canvas background color') }}" />

                <!-- Language Switcher -->
                <livewire:language-switcher />

                <!-- Theme Actions Menu (Kebab) -->
                @if ($currentTheme)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center justify-center p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150"
                            title="{{ __('Theme Actions') }}">
                            <x-heroicon-o-ellipsis-vertical class="w-5 h-5" />
                        </button>

                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-48 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 focus:outline-none"
                            :class="document.documentElement.dir === 'rtl' ? 'left-0 origin-top-left' :
                                'right-0 origin-top-right'">

                            <div class="py-1">
                                <!-- Export Theme -->
                                <button wire:click="exportTheme" @click="open = false"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-3" />
                                    {{ __('Export Theme') }}
                                </button>

                                <!-- Import Theme -->
                                <button @click="showImportConfirmModal = true; open = false"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-3" />
                                    {{ __('Import Theme') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal for Adding Block -->
        @if ($showBlockModal)
            @include('page-builder::livewire.builder.partials.blocks-modal', [
                'allBlocks' => $formattedBlocks,
            ])
        @endif

        <!-- Modal for Page Blocks -->
        @if ($showPageBlocksModal)
            @include('page-builder::livewire.builder.partials.page-blocks-modal', [
                'allPageBlocks' => $allPageBlocks,
            ])
        @endif

        <!-- Pages Modal -->
        @include('page-builder::livewire.builder.partials.pages-modal', [
            'pagesWithStatus' => $this->getPagesWithStatus(),
            'themeId' => $themeId,
        ])

        <!-- Copy From Modal -->
        @include('page-builder::livewire.builder.partials.pages-modal', [
            'pagesWithStatus' => $this->getPagesWithStatus(),
            'themeId' => $themeId,
            'modalMode' => 'copy',
        ])

        <!-- Copy Confirmation Modal -->
        <div x-show="showCopyConfirmationModal"
            class="fixed inset-0 z-53 flex items-center justify-center bg-black/40" style="display: none;"
            @click.outside="showCopyConfirmationModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 relative">
                <div class="text-center">
                    <!-- Warning Icon -->
                    <div
                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                    </div>

                    <!-- Title -->
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        {{ __('Confirm Copy') }}
                    </h3>

                    <!-- Message -->
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('Are you sure you want to copy components from') }} <span
                            class="font-semibold text-gray-900 dark:text-gray-100"
                            x-text="copySourcePageLabel"></span>{{ __('?') }}
                        {{ __('This will replace all current page content.') }}
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 justify-center">
                        <button @click="showCopyConfirmationModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-500 transition-all duration-150">
                            {{ __('Cancel') }}
                        </button>
                        <button
                            @click="
                        showCopyConfirmationModal = false;
                        $wire.copyComponentsFromPage(copySourcePageKey);
                    "
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-200 rounded-md transition-all duration-150">
                            {{ __('Yes, Replace Content') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Theme Confirmation Modal -->
        <div x-show="showImportConfirmModal"
            class="fixed inset-0 z-53 flex items-center justify-center bg-black/40" style="display: none;"
            x-cloak
            @keydown.escape.window="showImportConfirmModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 relative"
                @click.outside="showImportConfirmModal = false">
                <div class="text-center">
                    <!-- Warning Icon -->
                    <div
                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>

                    <!-- Title -->
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        {{ __('Import Theme Warning') }}
                    </h3>

                    <!-- Message -->
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('This will replace ALL pages in the current theme. All existing page content will be permanently deleted. This action cannot be undone.') }}
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 justify-center">
                        <button @click="showImportConfirmModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-500 transition-all duration-150">
                            {{ __('Cancel') }}
                        </button>
                        <button @click="showImportConfirmModal = false; showImportFileModal = true"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-200 rounded-md transition-all duration-150">
                            {{ __('I Understand, Continue') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Theme File Modal -->
        <div x-show="showImportFileModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
            @keydown.escape.window="showImportFileModal = false; $wire.set('importFile', null)">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showImportFileModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showImportFileModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'"
                    @click.outside="showImportFileModal = false; $wire.set('importFile', null)">
                    <form wire:submit="parseImportFile">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                                <div
                                    class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <x-heroicon-o-arrow-up-tray class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="mt-3 text-center w-full"
                                    :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                        'sm:mt-0 sm:ml-4 sm:text-left'">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                        id="modal-title">
                                        {{ __('Import Theme Pages') }}
                                    </h3>
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                            {{ __('Select a theme file to import.') }}
                                        </p>
                                        <div>
                                            <label for="importFile"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ __('Theme File') }}
                                            </label>
                                            <input type="file" wire:model="importFile" id="importFile"
                                                accept=".json,.encrypted"
                                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 dark:file:bg-pink-900/30 dark:file:text-pink-400 dark:hover:file:bg-pink-900/50">
                                            @error('importFile')
                                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                            <button type="submit" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-8 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                                <span wire:loading.remove wire:target="parseImportFile" class="flex items-center">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                                    {{ __('Continue') }}
                                </span>
                                <span wire:loading wire:target="parseImportFile" class="flex items-center">
                                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2 animate-spin" />
                                    {{ __('Loading...') }}
                                </span>
                            </button>
                            <button type="button"
                                @click="showImportFileModal = false; $wire.set('importFile', null)"
                                class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Page Selection Modal for Import -->
        @if ($showPageSelectionModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                        :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                                <div
                                    class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <x-heroicon-o-document-duplicate
                                        class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="mt-3 text-center w-full"
                                    :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                        'sm:mt-0 sm:ml-4 sm:text-left'">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                        id="modal-title">
                                        {{ __('Select Pages to Import') }}
                                    </h3>
                                    <div class="mt-4">
                                        <!-- Import Mode Selection -->
                                        <div class="space-y-3 mb-4">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="radio" wire:model.live="importMode" value="all"
                                                    class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-gray-700">
                                                <span
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300 rtl:mr-2 rtl:ml-0">
                                                    {{ __('Import All Pages') }}
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        ({{ count($importedPagesData) }}
                                                        {{ __('pages') }})
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="radio" wire:model.live="importMode" value="selected"
                                                    class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:bg-gray-700">
                                                <span
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300 rtl:mr-2 rtl:ml-0">
                                                    {{ __('Select Specific Pages') }}
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Page Selection List (only shown when importMode is 'selected') -->
                                        @if ($importMode === 'selected')
                                            <div
                                                class="border border-gray-200 dark:border-gray-600 rounded-lg max-h-64 overflow-y-auto">
                                                <!-- Select All / Deselect All -->
                                                <div
                                                    class="sticky top-0 bg-gray-50 dark:bg-gray-700 px-3 py-2 border-b border-gray-200 dark:border-gray-600 flex gap-2">
                                                    <button type="button" wire:click="selectAllPages"
                                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                        {{ __('Select All') }}
                                                    </button>
                                                    <span class="text-gray-300 dark:text-gray-500">|</span>
                                                    <button type="button" wire:click="deselectAllPages"
                                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                        {{ __('Deselect All') }}
                                                    </button>
                                                </div>
                                                <!-- Pages List -->
                                                <div class="divide-y divide-gray-200 dark:divide-gray-600">
                                                    @foreach ($importedPagesData as $pageData)
                                                        <label
                                                            class="flex items-center px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                                                            <input type="checkbox"
                                                                wire:click="togglePageSelection('{{ $pageData['key'] }}')"
                                                                @checked(in_array($pageData['key'], $selectedPageKeys))
                                                                class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:bg-gray-700">
                                                            <span
                                                                class="ml-2 text-sm text-gray-700 dark:text-gray-300 rtl:mr-2 rtl:ml-0">
                                                                {{ $pageData['key'] }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @if (empty($selectedPageKeys))
                                                <p class="text-xs text-red-500 mt-2">
                                                    {{ __('No pages selected') }}
                                                </p>
                                            @endif
                                        @endif

                                        <!-- Warning Message -->
                                        <div
                                            class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                            <div class="flex">
                                                <x-heroicon-o-exclamation-triangle
                                                    class="h-5 w-5 text-yellow-400 shrink-0" />
                                                <div class="ml-3 rtl:mr-3 rtl:ml-0">
                                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                                        @if ($importMode === 'all')
                                                            {{ __('This will replace ALL pages in the current theme.') }}
                                                        @else
                                                            {{ __('Selected pages will be replaced in the current theme.') }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                            <button type="button" wire:click="importThemePages" wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                @if ($importMode === 'selected' && empty($selectedPageKeys)) disabled @endif
                                class="inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-8 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                                <span wire:loading.remove wire:target="importThemePages" class="flex items-center">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2 rtl:ml-2 rtl:mr-0" />
                                    @if ($importMode === 'all')
                                        {{ __('Import All Pages') }}
                                    @else
                                        {{ __('Import :count Pages', ['count' => count($selectedPageKeys)]) }}
                                    @endif
                                </span>
                                <span wire:loading wire:target="importThemePages" class="flex items-center">
                                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2 animate-spin rtl:ml-2 rtl:mr-0" />
                                    {{ __('Importing...') }}
                                </span>
                            </button>
                            <button type="button" wire:click="closePageSelectionModal"
                                class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Theme Selector Modal -->
        @if ($showThemeSelector)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                        :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'">
                        <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                        Select a Theme
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Choose a theme to start building your page') }}
                                    </p>
                                </div>
                                <button wire:click="closeThemeSelector"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <x-heroicon-o-x-mark class="w-6 h-6" />
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                                @forelse($availableThemes as $theme)
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-all duration-200 cursor-pointer"
                                        wire:click="selectThemeForPage({{ $theme['id'] }})">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900 dark:text-white">
                                                    {{ $theme['name'] }}
                                                </h4>
                                                @if ($theme['description'])
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        {{ $theme['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div
                                            class="h-16 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                            <x-heroicon-o-paint-brush
                                                class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                                        </div>

                                        <button
                                            class="w-full mt-3 inline-flex items-center justify-center px-3 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md transition-all duration-150">
                                            <x-heroicon-o-paint-brush class="w-4 h-4 mr-2" />
                                            {{ __('Design Pages') }}
                                        </button>
                                    </div>
                                @empty
                                    <div class="col-span-full text-center py-8">
                                        <x-heroicon-o-paint-brush
                                            class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto" />
                                        <h4 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">
                                            {{ __('No themes available') }}</h4>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            {{ __('Create a theme first to start building pages.') }}</p>
                                        <a href="{{ route('page-builder.themes') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg transition-all duration-150">
                                            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                            {{ __('Create Theme') }}
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3">
                            <div class="flex justify-between">
                                <a href="{{ route('page-builder.themes') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-2" />
                                    Manage Themes
                                </a>
                                <button wire:click="closeThemeSelector"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content and Properties Panel -->
        <div class="flex flex-1 min-h-0">

            <!-- Properties Panel (Fixed/Sticky) -->
            <aside
                class="hidden lg:block w-[20%] h-[calc(100vh-56px)] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-lg overflow-y-auto">
                @livewire('block-properties')
            </aside>

            <!-- Main Section (Scrollable) -->
            <main class="flex-1 pt-10 pb-50 pr-0 overflow-auto min-h-0 w-[80%]"
                :style="`background-color: ${canvasBgColor}`">
                <div class="mx-auto @container"
                    :class="{
                        'w-[375px]': deviceMode === 'mobile',
                        'w-[767px]': deviceMode === 'tablet',
                        'w-full': deviceMode === 'desktop',
                    }"
                    style="font-size:0">
                    @foreach ($rows as $rowId => $row)
                        <livewire:row-block :edit-mode="true" :blocks="$row['blocks']" :rowId="$rowId" :properties="$row['properties'] ?? []"
                            :key="$rowId" />
                    @endforeach
                </div>
            </main>
        </div>
    </div>
    <style>
        /* Convert fixed positioning to absolute within the page builder design area */
        .builder-block .fixed {
            position: absolute !important;
        }
    </style>
</div>
