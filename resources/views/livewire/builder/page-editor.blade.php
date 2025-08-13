<div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900" x-data="{
    showPagesModal: false,
    deviceMode: 'desktop',
    loading: true,
    canPaste: false,
    pasteDataType: null,
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

                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
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
                                <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-2" />
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
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $theme['name'] }}
                                            </h4>
                                            @if ($theme['description'])
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    {{ $theme['description'] }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div
                                        class="h-16 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                        <x-heroicon-o-paint-brush class="w-6 h-6 text-gray-400 dark:text-gray-500" />
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
    <div class="flex flex-1 min-h-0" x-data="{
        propDefs: @js($propertyDefinitionsByHash ?? []),
        selected: { type: null, id: null, classHash: null, label: null, props: {} },
    }"
        x-on:block-selected.window="selected = { type: 'block', id: $event.detail.blockId, classHash: $event.detail.blockClass, label: null, props: $event.detail.properties || {} }"
        x-on:row-selected.window="selected = { type: 'row', id: $event.detail.rowId, classHash: '{{ md5(Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) }}', label: null, props: $event.detail.properties || {} }">

        <!-- Properties Panel (Fixed/Sticky) -->
        <aside
            class="hidden lg:block w-[20%] h-[calc(100vh-56px)] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-lg overflow-y-auto">
            @include('page-builder::livewire.builder.partials.properties-panel')
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
