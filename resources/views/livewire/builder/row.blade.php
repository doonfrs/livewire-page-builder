<div id="row-{{ $rowId }}" x-data="{
    selected: false
}"
    class="block-row border relative transition-all duration-300 ease-in-out group {{ $cssClasses }}"
    style="{{ $inlineStyles }} font-size:initial" :class="selected ? 'border-pink-500' : 'border-gray-300'"
    x-on:row-selected.window="selected = $event.detail.rowId == '{{ $rowId }}'"
    x-on:block-selected.window="selected = false">
    <div class="block-row-inner">
        <!-- Elementor-style Row Controls -->
        <div
            class="absolute top-[-35px] left-1/2 -translate-x-1/2 bg-pink-500 shadow-lg px-1 py-1 rounded-lg flex items-center space-x-1 z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none group-hover:pointer-events-auto">
            <!-- Select Button -->
            <button wire:click="rowSelected()"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="Select Row">
                <x-heroicon-o-cursor-arrow-rays class="w-5 h-5" />
            </button>

            <!-- Handle/Options Button -->
            <button @click="$dispatch('toggle-row-options', {rowId: '{{ $rowId }}'})"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="More Options">
                <x-heroicon-o-ellipsis-horizontal class="w-5 h-5" />
            </button>

            <!-- Add Block Button -->
            <button wire:click="openBlockModal()"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="{{ __('Add Block') }}">
                <x-heroicon-o-plus class="w-5 h-5" />
            </button>
        </div>

        <!-- Hidden Drawer for Row Tools (appears on click of handle) -->
        <div x-data="{ open: false }"
            x-on:toggle-row-options.window="if($event.detail.rowId === '{{ $rowId }}') open = !open"
            class="absolute top-[-35px] left-1/2 -translate-x-1/2 z-51">
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-2" @click.outside="open = false"
                class="absolute top-[45px] left-1/2 -translate-x-1/2 bg-white shadow-xl rounded-lg border border-gray-200 py-2 w-[250px] dark:bg-gray-800 dark:border-gray-700">

                <div class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 mb-1">
                    {{ __('Row Actions') }}
                </div>

                <!-- Row Select Button -->
                <button wire:click="rowSelected()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700"
                    title="{{ __('Select Row') }}">
                    <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 ms-0 me-3 rtl:rotate-180" />
                    <span>{{ __('Select') }}</span>
                </button>

                <!-- Copy Row Button -->
                <button wire:click="copyRow()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700"
                    title="{{ __('Copy Row') }}">
                    <x-heroicon-o-clipboard-document class="w-4 h-4 ms-0 me-3" />
                    <span>{{ __('Copy') }}</span>
                </button>

                <!-- Paste Row (combined with before/after options) -->
                <div class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center flex-1">
                        <x-heroicon-o-clipboard-document-check class="w-4 h-4 ms-0 me-3" />
                        <span>{{ __('Paste') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button 
                            @click="
                                navigator.clipboard.readText().then(text => {
                                    if (text) {
                                        try {
                                            const data = JSON.parse(text);
                                            if (data && data.type) {
                                                $dispatch('paste-from-clipboard', {
                                                    clipboardData: text,
                                                    targetRowId: '{{ $rowId }}',
                                                    position: 'before'
                                                });
                                            } else {
                                                console.error('{{ __("Invalid clipboard data format") }}');
                                            }
                                        } catch (e) {
                                            console.error('{{ __("Failed to parse clipboard data:") }}', e);
                                        }
                                    }
                                }).catch(err => {
                                    console.error('{{ __("Failed to read clipboard contents:") }}', err);
                                });
                                open = false;
                            "
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Paste Before Row') }}">
                            <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                            {{ __('Before') }}
                        </button>
                        <button 
                            @click="
                                navigator.clipboard.readText().then(text => {
                                    if (text) {
                                        try {
                                            const data = JSON.parse(text);
                                            if (data && data.type) {
                                                $dispatch('paste-from-clipboard', {
                                                    clipboardData: text,
                                                    targetRowId: '{{ $rowId }}',
                                                    position: 'after'
                                                });
                                            } else {
                                                console.error('{{ __("Invalid clipboard data format") }}');
                                            }
                                        } catch (e) {
                                            console.error('{{ __("Failed to parse clipboard data:") }}', e);
                                        }
                                    }
                                }).catch(err => {
                                    console.error('{{ __("Failed to read clipboard contents:") }}', err);
                                });
                                open = false;
                            "
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Paste After Row') }}">
                            <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                            {{ __('After') }}
                        </button>
                    </div>
                </div>

                <!-- Move Row (combined Up/Down) -->
                <div class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center flex-1">
                        <x-heroicon-o-arrows-up-down class="w-4 h-4 ms-0 me-3" />
                        <span>{{ __('Move') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button wire:click="moveRowUp()" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Move Row Up') }}">
                            <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                            {{ __('Up') }}
                        </button>
                        <button wire:click="moveRowDown()" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Move Row Down') }}">
                            <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                            {{ __('Down') }}
                        </button>
                    </div>
                </div>

                <!-- Add Row (combined Before/After) -->
                <div class="flex items-center w-full px-3 py-2 text-green-700 dark:text-green-400 border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center flex-1">
                        <x-heroicon-o-plus class="w-4 h-4 ms-0 me-3 text-green-500" />
                        <span>{{ __('Add Row') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button wire:click="$dispatch('addRow', {beforeRowId: '{{ $rowId }}'})" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-green-50 dark:hover:bg-green-900/20 border border-green-100 dark:border-green-800/50"
                            title="{{ __('Add Row Before') }}">
                            <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                            {{ __('Before') }}
                        </button>
                        <button wire:click="$dispatch('addRow', {afterRowId: '{{ $rowId }}'})" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-green-50 dark:hover:bg-green-900/20 border border-green-100 dark:border-green-800/50"
                            title="{{ __('Add Row After') }}">
                            <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                            {{ __('After') }}
                        </button>
                    </div>
                </div>

                <!-- Remove Row Button -->
                <button
                    @click="
                confirm('{{ __('Are you sure you want to delete this row?') }}') 
                && $dispatch('deleteRow', {rowId: '{{ $rowId }}'})
                "
                    @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                    title="{{ __('Remove Row') }}">
                    <x-heroicon-o-trash class="w-4 h-4 ms-0 me-3 text-red-500" />
                    <span>{{ __('Remove Row') }}</span>
                </button>
            </div>
        </div>

        <div class="row-blocks pt-4 pb-4 {{ $rowCssClasses }}">
            @foreach ($blocks as $blockId => $block)
            @livewire(
            'builder-block',
            [
            'blockAlias' => $block['alias'],
            'blockId' => $blockId,
            'rowId' => $rowId,
            'properties' => $block['properties'] ?? [],
            'editMode' => true,
            ],
            key($blockId)
            )
            @endforeach
        </div>
    </div>
</div>