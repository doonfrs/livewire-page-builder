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
            class="absolute top-[-15px] left-1/2 -translate-x-1/2 bg-pink-500 shadow-lg px-1 py-1 rounded-lg flex items-center space-x-1 z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none group-hover:pointer-events-auto">
            <!-- Select Button -->
            <button wire:click="rowSelected()"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="Select Row">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                </svg>
            </button>

            <!-- Handle/Options Button -->
            <button @click="$dispatch('toggle-row-options', {rowId: '{{ $rowId }}'})"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="More Options">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
            </button>

            <!-- Add Block Button -->
            <button wire:click="openBlockModal()"
                class="w-7 h-7 flex items-center justify-center text-white hover:bg-pink-600 rounded transition-colors duration-150"
                title="{{ __('Add Block') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
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

                <div
                    class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 mb-1">
                    {{ __('Row Actions') }}
                </div>

                <!-- Row Select Button -->
                <button wire:click="rowSelected()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700"
                    title="{{ __('Select Row') }}">
                    <svg class="w-4 h-4 ms-0 me-3 rtl:rotate-180" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                    </svg>
                    <span>{{ __('Select') }}</span>
                </button>

                <!-- Copy Row Button -->
                <button wire:click="copyRow()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700"
                    title="{{ __('Copy Row') }}">
                    <svg class="w-4 h-4 ms-0 me-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>{{ __('Copy') }}</span>
                </button>

                <!-- Paste Row (combined with before/after options) -->
                <div
                    class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center flex-1">
                        <svg class="w-4 h-4 ms-0 me-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.75m-8.25-8.25 5.25 5.25 10.5-10.5" />
                        </svg>
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
                                                console.error('{{ __('Invalid clipboard data format') }}');
                                            }
                                        } catch (e) {
                                            console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                        }
                                    }
                                }).catch(err => {
                                    console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                });
                                open = false;
                            "
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Paste Before Row') }}">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" />
                            </svg>
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
                                                console.error('{{ __('Invalid clipboard data format') }}');
                                            }
                                        } catch (e) {
                                            console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                        }
                                    }
                                }).catch(err => {
                                    console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                });
                                open = false;
                            "
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Paste After Row') }}">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                            </svg>
                            {{ __('After') }}
                        </button>
                    </div>
                </div>

                <!-- Move Row (combined Up/Down) -->
                <div
                    class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center flex-1">
                        <svg class="w-4 h-4 ms-0 me-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                        <span>{{ __('Move') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button wire:click="moveRowUp()" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Move Row Up') }}">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" />
                            </svg>
                            {{ __('Up') }}
                        </button>
                        <button wire:click="moveRowDown()" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-600"
                            title="{{ __('Move Row Down') }}">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                            </svg>
                            {{ __('Down') }}
                        </button>
                    </div>
                </div>

                <!-- Add Row (combined Before/After) -->
                <div
                    class="flex items-center w-full px-3 py-2 text-green-700 dark:text-green-400 border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center flex-1">
                        <svg class="w-4 h-4 ms-0 me-3 text-green-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ __('Add Row') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button wire:click="$dispatch('addRow', {beforeRowId: '{{ $rowId }}'})"
                            @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-green-50 dark:hover:bg-green-900/20 border border-green-100 dark:border-green-800/50"
                            title="{{ __('Add Row Before') }}">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" />
                            </svg>
                            {{ __('Before') }}
                        </button>
                        <button wire:click="$dispatch('addRow', {afterRowId: '{{ $rowId }}'})"
                            @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-green-50 dark:hover:bg-green-900/20 border border-green-100 dark:border-green-800/50"
                            title="{{ __('Add Row After') }}">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                            </svg>
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
                    <svg class="w-4 h-4 ms-0 me-3 text-red-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>{{ __('Remove Row') }}</span>
                </button>
            </div>
        </div>

        <div class="row-blocks pt-10 pb-10 {{ $rowCssClasses }}">
            @foreach ($blocks as $blockId => $block)
                @livewire(
                    'builder-block',
                    [
                        'blockAlias' => $block['alias'],
                        'blockId' => $blockId,
                        'rowId' => $rowId,
                        'properties' => $block['properties'] ?? [],
                        'blocks' => $block['blocks'] ?? [], // Pass nested blocks for RowBlock
                        'editMode' => true,
                    ],
                    key($blockId)
                )
            @endforeach
        </div>
    </div>
</div>
