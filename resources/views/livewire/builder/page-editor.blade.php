<div>
    <div class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900" x-data="{
        showPagesModal: false,
        showCopyFromModal: false,
        showCopyConfirmationModal: false,
        copySourcePageKey: null,
        copySourcePageLabel: null,
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
                <svg class="w-16 h-16 mx-auto text-pink-500 animate-spin" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4">
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
            <svg class="w-5 h-5 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
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
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">{{ $currentTheme->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5">
                                </path>
                            </svg>
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
                                            <svg class="w-4 h-4 mr-2 text-pink-500" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m4.5 12.75 6 6 9-13.5"></path>
                                            </svg>
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
                                    <svg class="w-4 h-4 mr-2 ml-2" fill="none" stroke="currentColor"
                                        stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                    </svg>
                                    {{ __('Manage Themes') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <button wire:click="openThemeSelector"
                        class="flex items-center gap-2 px-4 py-2 border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-md hover:bg-yellow-100 dark:hover:bg-yellow-900/50 focus:ring-2 focus:ring-yellow-200 transition-all duration-150 text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z">
                            </path>
                        </svg>
                        <span class="hidden sm:inline">{{ __('Select Theme') }}</span>
                    </button>
                @endif

                <!-- Add Button -->
                <button
                    class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium"
                    wire:click="addRow" title="{{ __('Add Row') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                </button>
                <!-- List Blocks Button -->
                <button
                    class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium"
                    wire:click="$dispatch('openPageBlocksModal')" title="{{ __('List Blocks') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z">
                        </path>
                    </svg>
                </button>
                <!-- Pages Button -->
                <button x-on:click="showPagesModal = true"
                    class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-200 transition-all duration-150 text-sm font-medium"
                    title="{{ __('Open Pages') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">{{ $this->getCurrentPageLabel() }}</span>
                    @if ($this->isCurrentPageBlock())
                        <span
                            class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-700"
                            title="{{ __('This is a reusable page block') }}">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9">
                                </path>
                            </svg>
                            {{ __('Block') }}
                        </span>
                    @endif
                </button>

                <!-- Copy From Button -->
                <button x-on:click="showCopyFromModal = true"
                    class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-green-200 transition-all duration-150 text-sm font-medium"
                    title="{{ __('Copy components from another page') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">{{ __('Copy From') }}</span>
                </button>
                <!-- Preview Button -->
                <a :href="'/page-builder/page/view/' + @js($pageKey ?? '') + (@js($themeId ?? '') ? '/' +
                    @js($themeId ?? '') : '')"
                    target="_blank"
                    class="flex items-center gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-200 transition-all duration-150 text-sm font-medium"
                    title="{{ __('Preview Page') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z">
                        </path>
                    </svg>
                </a>
                <!-- Save Button with animation placeholder -->
                <button
                    class="flex items-center w-14 gap-1 px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 text-sm font-medium relative"
                    wire:click="$dispatch('save-page')" x-data="{ saved: false }"
                    x-on:click="saved = true; setTimeout(() => saved = false, 1200);" title="{{ __('Save Page') }}">
                    <span x-show="!saved" class="flex items-center gap-1 justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                        </svg>
                    </span>
                    <span x-show="saved" x-transition class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500 animate-bounce" fill="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z"
                                clip-rule="evenodd"></path>
                        </svg>
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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3">
                            </path>
                        </svg>
                    </button>
                    <button :class="deviceMode === 'tablet' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''"
                        x-on:click="deviceMode = 'tablet'"
                        class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150 border-r border-gray-200 dark:border-gray-800 last:border-r-0"
                        title="Tablet View">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z">
                            </path>
                        </svg>
                    </button>
                    <button :class="deviceMode === 'desktop' ? 'bg-pink-100 dark:bg-pink-900 text-pink-600' : ''"
                        x-on:click="deviceMode = 'desktop'"
                        class="px-4 py-2 text-sm font-medium flex items-center gap-1 hover:bg-gray-100 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition-all duration-150"
                        title="Desktop View">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 5.25Z">
                            </path>
                        </svg>
                    </button>
                </div>
                <!-- Language Switcher -->
                <livewire:language-switcher />
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
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z">
                            </path>
                        </svg>
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
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12">
                                        </path>
                                    </svg>
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
                                            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none"
                                                stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42">
                                                </path>
                                            </svg>
                                        </div>

                                        <button
                                            class="w-full mt-3 inline-flex items-center justify-center px-3 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md transition-all duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42">
                                                </path>
                                            </svg>
                                            {{ __('Design Pages') }}
                                        </button>
                                    </div>
                                @empty
                                    <div class="col-span-full text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto" fill="none"
                                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42">
                                            </path>
                                        </svg>
                                        <h4 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">
                                            {{ __('No themes available') }}</h4>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            {{ __('Create a theme first to start building pages.') }}</p>
                                        <a href="{{ route('page-builder.themes') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg transition-all duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15"></path>
                                            </svg>
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
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                    </svg>
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
            <main class="flex-1 pt-10 pb-50 pr-0 bg-gray-50 dark:bg-gray-900 overflow-auto min-h-0 w-[80%]">
                <div class="mx-auto @container"
                    :class="{
                        'w-[375px]': deviceMode === 'mobile',
                        'w-[768px]': deviceMode === 'tablet',
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
