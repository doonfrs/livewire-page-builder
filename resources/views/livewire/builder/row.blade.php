<div id="row-{{ $rowId }}" x-data="{
    selected: false,
    showDeleteModal: false,
    deleteMessage: '',
    deleteAction: null,
    clipboardHasRow: false,
    checkClipboard() {
        navigator.clipboard.readText().then(text => {
            if (text) {
                try {
                    const data = JSON.parse(text);
                    this.clipboardHasRow = data && data.type === 'RowBlock';
                } catch (e) {
                    this.clipboardHasRow = false;
                }
            } else {
                this.clipboardHasRow = false;
            }
        }).catch(() => {
            this.clipboardHasRow = false;
        });
    }
}"
    class="block-row border relative transition-all duration-300 ease-in-out group {{ $cssClasses }}"
    style="{{ $inlineStyles }} font-size:initial" {!! $dataAttributes !!}
    :class="selected ? 'border-pink-500' : 'border-gray-300'"
    x-on:row-selected.window="selected = $event.detail.rowId == '{{ $rowId }}'"
    x-on:block-selected.window="selected = false">
    <div class="block-row-inner {{ $isNested ? 'relative' : '' }} h-full">
        <!-- Row Controls -->
        <div
            class="row-control absolute top-[-15px] left-1/2 -translate-x-1/2 bg-pink-500 shadow-lg px-1 py-1 rounded-lg flex items-center space-x-1 z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none group-hover:pointer-events-auto">
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
            x-on:toggle-row-options.window="if($event.detail.rowId === '{{ $rowId }}') { open = !open; if(open) checkClipboard(); }"
            class="absolute {{ $isNested ? 'top-[-35px]' : 'top-[-35px]' }} left-1/2 -translate-x-1/2 z-51">
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
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Select Row') }}">
                    <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 ms-0 me-3 rtl:rotate-180" />
                    <span>{{ __('Select') }}</span>
                </button>

                <!-- Copy Row Button -->
                <button wire:click="copyRow()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Copy Row') }}">
                    <x-heroicon-o-clipboard-document class="w-4 h-4 ms-0 me-3" />
                    <span>{{ __('Copy') }}</span>
                </button>

                <!-- Cut Row Button -->
                <button wire:click="cutRow()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Cut Row') }}">
                    <x-heroicon-o-scissors class="w-4 h-4 ms-0 me-3" />
                    <span>{{ __('Cut') }}</span>
                </button>

                <!-- Duplicate Row Button -->
                <button wire:click="duplicateRow()" @click="open = false"
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer"
                    wire:loading.class="opacity-50 cursor-wait" wire:loading.attr="disabled" wire:target="duplicateRow"
                    title="{{ __('Duplicate Row') }}">
                    <x-heroicon-o-document-duplicate class="w-4 h-4 ms-0 me-3" wire:loading.remove
                        wire:target="duplicateRow" />
                    <svg wire:loading wire:target="duplicateRow" class="animate-spin w-4 h-4 ms-0 me-3"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>{{ __('Duplicate') }}</span>
                </button>

                <!-- Paste Row -->
                @if ($isNested)
                    <!-- Nested Row: Show Before/After/Inside options -->
                    <div class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                        @click="
                            console.log('Nested Row Paste INSIDE clicked for row {{ $rowId }}');
                            navigator.clipboard.readText().then(text => {
                                console.log('Clipboard read successful for nested row, length:', text ? text.length : 0);
                                if (text) {
                                    try {
                                        const data = JSON.parse(text);
                                        console.log('Clipboard data parsed for nested row:', data);
                                        if (data && data.type) {
                                            console.log('Dispatching paste-from-clipboard event with position: inside, targetRowId: {{ $rowId }}');
                                            $dispatch('paste-from-clipboard', {
                                                clipboardData: text,
                                                targetRowId: '{{ $rowId }}',
                                                position: 'inside'
                                            });
                                        } else {
                                            console.error('{{ __('Invalid clipboard data format') }}', data);
                                        }
                                    } catch (e) {
                                        console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                    }
                                } else {
                                    console.warn('Clipboard is empty for nested row');
                                }
                            }).catch(err => {
                                console.error('{{ __('Failed to read clipboard contents:') }}', err);
                            });
                            open = false;
                        ">
                        <div class="flex items-center flex-1">
                            <x-heroicon-o-clipboard-document-check class="w-4 h-4 ms-0 me-3" />
                            <span>{{ __('Paste') }}</span>
                        </div>
                        <div class="flex space-x-2 rtl:space-x-reverse">
                            <button x-show="clipboardHasRow"
                                @click.stop="
                                    console.log('Nested Row Paste BEFORE clicked for row {{ $rowId }}');
                                    navigator.clipboard.readText().then(text => {
                                        console.log('Clipboard read for before (outside), length:', text ? text.length : 0);
                                        if (text) {
                                            try {
                                                const data = JSON.parse(text);
                                                console.log('Parsed data for before (outside):', data);
                                                if (data && data.type) {
                                                    console.log('Dispatching paste-from-clipboard BEFORE (outside) for row {{ $rowId }}');
                                                    $dispatch('paste-from-clipboard', {
                                                        clipboardData: text,
                                                        targetRowId: '{{ $rowId }}',
                                                        position: 'before'
                                                    });
                                                } else {
                                                    console.error('{{ __('Invalid clipboard data format') }}', data);
                                                }
                                            } catch (e) {
                                                console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                            }
                                        } else {
                                            console.warn('Clipboard is empty for before (outside)');
                                        }
                                    }).catch(err => {
                                        console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                    });
                                    open = false;
                                "
                                class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                                title="{{ __('Paste Before Row (Outside)') }}">
                                <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                                {{ __('Before') }}
                            </button>
                            <button
                                @click.stop="
                                    console.log('Nested Row Paste INSIDE clicked for row {{ $rowId }}');
                                    navigator.clipboard.readText().then(text => {
                                        console.log('Clipboard read for inside, length:', text ? text.length : 0);
                                        if (text) {
                                            try {
                                                const data = JSON.parse(text);
                                                console.log('Parsed data for inside:', data);
                                                if (data && data.type) {
                                                    console.log('Dispatching paste-from-clipboard INSIDE for row {{ $rowId }}');
                                                    $dispatch('paste-from-clipboard', {
                                                        clipboardData: text,
                                                        targetRowId: '{{ $rowId }}',
                                                        position: 'inside'
                                                    });
                                                } else {
                                                    console.error('{{ __('Invalid clipboard data format') }}', data);
                                                }
                                            } catch (e) {
                                                console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                            }
                                        } else {
                                            console.warn('Clipboard is empty for inside');
                                        }
                                    }).catch(err => {
                                        console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                    });
                                    open = false;
                                "
                                class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                                title="{{ __('Paste Inside Row') }}">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-3 h-3 inline-block" />
                                {{ __('Inside') }}
                            </button>
                            <button x-show="clipboardHasRow"
                                @click.stop="
                                    console.log('Nested Row Paste AFTER clicked for row {{ $rowId }}');
                                    navigator.clipboard.readText().then(text => {
                                        console.log('Clipboard read for after (outside), length:', text ? text.length : 0);
                                        if (text) {
                                            try {
                                                const data = JSON.parse(text);
                                                console.log('Parsed data for after (outside):', data);
                                                if (data && data.type) {
                                                    console.log('Dispatching paste-from-clipboard AFTER (outside) for row {{ $rowId }}');
                                                    $dispatch('paste-from-clipboard', {
                                                        clipboardData: text,
                                                        targetRowId: '{{ $rowId }}',
                                                        position: 'after'
                                                    });
                                                } else {
                                                    console.error('{{ __('Invalid clipboard data format') }}', data);
                                                }
                                            } catch (e) {
                                                console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                            }
                                        } else {
                                            console.warn('Clipboard is empty for after (outside)');
                                        }
                                    }).catch(err => {
                                        console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                    });
                                    open = false;
                                "
                                class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                                title="{{ __('Paste After Row (Outside)') }}">
                                <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                                {{ __('After') }}
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Root Row: Show Before/After/Inside options -->
                    <div class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                        @click="
                            console.log('Root Row Paste INSIDE clicked for row {{ $rowId }}');
                            navigator.clipboard.readText().then(text => {
                                console.log('Clipboard read successful for root row, length:', text ? text.length : 0);
                                if (text) {
                                    try {
                                        const data = JSON.parse(text);
                                        console.log('Clipboard data parsed for root row:', data);
                                        if (data && data.type) {
                                            console.log('Dispatching paste-from-clipboard event with position: inside, targetRowId: {{ $rowId }}');
                                            $dispatch('paste-from-clipboard', {
                                                clipboardData: text,
                                                targetRowId: '{{ $rowId }}',
                                                position: 'inside'
                                            });
                                        } else {
                                            console.error('{{ __('Invalid clipboard data format') }}', data);
                                        }
                                    } catch (e) {
                                        console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                    }
                                } else {
                                    console.warn('Clipboard is empty for root row');
                                }
                            }).catch(err => {
                                console.error('{{ __('Failed to read clipboard contents:') }}', err);
                            });
                            open = false;
                        ">
                        <div class="flex items-center flex-1">
                            <x-heroicon-o-clipboard-document-check class="w-4 h-4 ms-0 me-3" />
                            <span>{{ __('Paste') }}</span>
                        </div>
                        <div class="flex space-x-2 rtl:space-x-reverse">
                            <button x-show="clipboardHasRow"
                                @click.stop="
                                    console.log('Root Row Paste BEFORE clicked for row {{ $rowId }}');
                                    navigator.clipboard.readText().then(text => {
                                        console.log('Clipboard read for before, length:', text ? text.length : 0);
                                        if (text) {
                                            try {
                                                const data = JSON.parse(text);
                                                console.log('Parsed data for before:', data);
                                                if (data && data.type) {
                                                    console.log('Dispatching paste-from-clipboard BEFORE for row {{ $rowId }}');
                                                    $dispatch('paste-from-clipboard', {
                                                        clipboardData: text,
                                                        targetRowId: '{{ $rowId }}',
                                                        position: 'before'
                                                    });
                                                } else {
                                                    console.error('{{ __('Invalid clipboard data format') }}', data);
                                                }
                                            } catch (e) {
                                                console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                            }
                                        } else {
                                            console.warn('Clipboard is empty for before');
                                        }
                                    }).catch(err => {
                                        console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                    });
                                    open = false;
                                "
                                class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                                title="{{ __('Paste Before Row') }}">
                                <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                                {{ __('Before') }}
                            </button>
                            <button
                                @click.stop="
                                    console.log('Root Row Paste INSIDE clicked for row {{ $rowId }}');
                                    navigator.clipboard.readText().then(text => {
                                        console.log('Clipboard read for inside, length:', text ? text.length : 0);
                                        if (text) {
                                            try {
                                                const data = JSON.parse(text);
                                                console.log('Parsed data for inside:', data);
                                                if (data && data.type) {
                                                    console.log('Dispatching paste-from-clipboard INSIDE for row {{ $rowId }}');
                                                    $dispatch('paste-from-clipboard', {
                                                        clipboardData: text,
                                                        targetRowId: '{{ $rowId }}',
                                                        position: 'inside'
                                                    });
                                                } else {
                                                    console.error('{{ __('Invalid clipboard data format') }}', data);
                                                }
                                            } catch (e) {
                                                console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                            }
                                        } else {
                                            console.warn('Clipboard is empty for inside');
                                        }
                                    }).catch(err => {
                                        console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                    });
                                    open = false;
                                "
                                class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                                title="{{ __('Paste Inside Row') }}">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-3 h-3 inline-block" />
                                {{ __('Inside') }}
                            </button>
                            <button x-show="clipboardHasRow"
                                @click.stop="
                                    console.log('Root Row Paste AFTER clicked for row {{ $rowId }}');
                                    navigator.clipboard.readText().then(text => {
                                        console.log('Clipboard read for after, length:', text ? text.length : 0);
                                        if (text) {
                                            try {
                                                const data = JSON.parse(text);
                                                console.log('Parsed data for after:', data);
                                                if (data && data.type) {
                                                    console.log('Dispatching paste-from-clipboard AFTER for row {{ $rowId }}');
                                                    $dispatch('paste-from-clipboard', {
                                                        clipboardData: text,
                                                        targetRowId: '{{ $rowId }}',
                                                        position: 'after'
                                                    });
                                                } else {
                                                    console.error('{{ __('Invalid clipboard data format') }}', data);
                                                }
                                            } catch (e) {
                                                console.error('{{ __('Failed to parse clipboard data:') }}', e);
                                            }
                                        } else {
                                            console.warn('Clipboard is empty for after');
                                        }
                                    }).catch(err => {
                                        console.error('{{ __('Failed to read clipboard contents:') }}', err);
                                    });
                                    open = false;
                                "
                                class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                                title="{{ __('Paste After Row') }}">
                                <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                                {{ __('After') }}
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Move Row (combined Up/Down) -->
                <div class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    wire:click="moveRowDown()" @click="open = false">
                    <div class="flex items-center flex-1">
                        <x-heroicon-o-arrows-right-left class="w-4 h-4 ms-0 me-3" />
                        <span>{{ __('Move') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button wire:click.stop="moveRowUp()" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                            title="{{ __('Move Row Up') }}">
                            <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                            {{ __('Up') }}
                        </button>
                        <button wire:click.stop="moveRowDown()" @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                            title="{{ __('Move Row Down') }}">
                            <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                            {{ __('Down') }}
                        </button>
                    </div>
                </div>

                <!-- Add Row (combined Before/After) -->
                <div class="flex items-center w-full px-3 py-2 text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    wire:click="$dispatch('addRow', {afterRowId: '{{ $rowId }}'})" @click="open = false">
                    <div class="flex items-center flex-1">
                        <x-heroicon-o-plus class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
                        <span>{{ __('Add Row') }}</span>
                    </div>
                    <div class="flex space-x-2 rtl:space-x-reverse">
                        <button wire:click.stop="$dispatch('addRow', {beforeRowId: '{{ $rowId }}'})"
                            @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                            title="{{ __('Add Row Before') }}">
                            <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                            {{ __('Before') }}
                        </button>
                        <button wire:click.stop="$dispatch('addRow', {afterRowId: '{{ $rowId }}'})"
                            @click="open = false"
                            class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                            title="{{ __('Add Row After') }}">
                            <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                            {{ __('After') }}
                        </button>
                    </div>
                </div>

                <!-- Remove Row Button -->
                <button
                    @click="
                        open = false;
                        deleteMessage = '{{ __('Are you sure you want to delete this row?') }}';
                        deleteAction = () => {
                            showDeleteModal = false;
                            setTimeout(() => $dispatch('deleteRow', { rowId: '{{ $rowId }}' }), 100);
                        };
                        showDeleteModal = true;
                    "
                    class="flex items-center w-full px-3 py-2 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Remove Row') }}">
                    <x-heroicon-o-trash class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
                    <span>{{ __('Remove Row') }}</span>
                </button>
            </div>
        </div>

        <div class="row-blocks pt-10 pb-10 {{ $rowCssClasses }}" style="font-size:0">
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

    <!-- Delete Confirmation Modal -->
    @include('page-builder::livewire.builder.partials.delete-confirmation-modal')
</div>
