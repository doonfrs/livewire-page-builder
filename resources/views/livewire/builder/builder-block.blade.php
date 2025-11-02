<div id="block-{{ $blockId }}" x-data="{
    selected: false,
    showContextMenu: false,
    x: 0,
    y: 0,
    showDeleteModal: false,
    deleteMessage: '',
    deleteAction: null,
    calculatePosition(mouseX, mouseY) {
        const menuWidth = 280;
        const menuHeight = 500;
        const padding = 10;

        let x = mouseX;
        let y = mouseY;

        // Check if menu would overflow right edge
        if (x + menuWidth > window.innerWidth - padding) {
            x = window.innerWidth - menuWidth - padding;
        }

        // Check if menu would overflow bottom edge
        if (y + menuHeight > window.innerHeight - padding) {
            y = window.innerHeight - menuHeight - padding;
        }

        // Ensure menu doesn't go off the left or top edges
        if (x < padding) x = padding;
        if (y < padding) y = padding;

        return { x, y };
    }
}"
    class="{{ $cssClasses }} border transition-all duration-300 ease-in-out" style="{{ $inlineStyles }}"
    {!! $dataAttributes !!} :class="selected ? 'border-blue-500' : 'border-gray-300'"
    x-on:block-selected.window="selected = $event.detail.blockId == '{{ $blockId }}'"
    x-on:row-selected.window="selected = false"
    @if (!$isRowBlock) @contextmenu.prevent="
        Livewire.dispatch('show-block-context-menu', {
            blockId: '{{ $blockId }}',
            x: $event.clientX,
            y: $event.clientY
        });
        $wire.blockSelected();
    " x-on:show-block-context-menu.window="
        if ($event.detail.blockId === '{{ $blockId }}') {
            const pos = calculatePosition($event.detail.x, $event.detail.y);
            x = pos.x;
            y = pos.y;
            showContextMenu = true;
        } else {
            showContextMenu = false;
        }
    " > @endif
    <div class="relative h-full content-center">
    @if ($isRowBlock)
        <!-- For RowBlocks, add minimal click handling that doesn't interfere with inner blocks -->
        <div class="builder-block relative h-full" @click.self="$wire.blockSelected()">
            @if (!$classExists)
                <div class="text-red-500">{{ __('Unknown block') }}: {{ $blockAlias }}</div>
            @else
                @livewire($blockAlias, $componentProperties, key($blockId . '-' . md5(json_encode($componentProperties))))
            @endif
        </div>
    @else
        <!-- For regular blocks, use the normal click handling -->
        <div class="cursor-pointer" wire:click="blockSelected()">
            <div class="builder-block relative">
                @if (!$classExists)
                    <div class="text-red-500">{{ __('Unknown block') }}: {{ $blockAlias }}</div>
                @else
                    @livewire($blockAlias, $componentProperties, key($blockId . '-' . md5(json_encode($componentProperties))))
                @endif
                <div class="absolute inset-0 z-50" style="pointer-events: all;"></div>
            </div>
        </div>
    @endif
    <!-- Context Menu UI -->
    <template x-teleport="body">
        <div x-show="showContextMenu" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" :style="`position: fixed; left: ${x}px; top: ${y}px;`"
            class="context-menu bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-2 w-[280px] z-[9999]"
            @click.outside="showContextMenu = false">

        <div
            class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700 mb-1">
            {{ __('Block Actions') }}
        </div>

        <button wire:click="blockSelected(); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer">
            <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
            <span>{{ __('Select') }}</span>
        </button>
        <button wire:click="copyBlock(); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer">
            <x-heroicon-o-clipboard-document class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
            <span>{{ __('Copy') }}</span>
        </button>

        <button wire:click="cutBlock(); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer">
            <x-heroicon-o-scissors class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
            <span>{{ __('Cut') }}</span>
        </button>

        <button wire:click="duplicateBlock(); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer"
            wire:loading.class="opacity-50 cursor-wait"
            wire:loading.attr="disabled"
            wire:target="duplicateBlock">
            <x-heroicon-o-document-duplicate class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" wire:loading.remove wire:target="duplicateBlock" />
            <svg wire:loading wire:target="duplicateBlock" class="animate-spin w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('Duplicate') }}</span>
        </button>

        <!-- Paste Block (combined before/after options) -->
        <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
            @click="
                console.log('Block Paste clicked - reading clipboard for block {{ $blockId }}');
                navigator.clipboard.readText().then(text => {
                    console.log('Clipboard read successful, length:', text ? text.length : 0);
                    if (text) {
                        try {
                            const data = JSON.parse(text);
                            console.log('Clipboard data parsed:', data);
                            if (data && data.type) {
                                console.log('Dispatching paste-from-clipboard event with position: after, targetBlockId: {{ $blockId }}');
                                $dispatch('paste-from-clipboard', {
                                    clipboardData: text,
                                    targetBlockId: '{{ $blockId }}',
                                    position: 'after'
                                });
                            } else {
                                console.error('{{ __('Invalid clipboard data format') }}', data);
                            }
                        } catch (e) {
                            console.error('{{ __('Failed to parse clipboard data:') }}', e);
                        }
                    } else {
                        console.warn('Clipboard is empty');
                    }
                }).catch(err => {
                    console.error('{{ __('Failed to read clipboard contents:') }}', err);
                });
                showContextMenu = false;
            ">
            <div class="flex items-center flex-1">
                <x-heroicon-o-clipboard-document-check class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
                <span>{{ __('Paste') }}</span>
            </div>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <button
                    @click.stop="
                            console.log('Block Paste BEFORE clicked for block {{ $blockId }}');
                            navigator.clipboard.readText().then(text => {
                                console.log('Clipboard read for before, length:', text ? text.length : 0);
                                if (text) {
                                    try {
                                        const data = JSON.parse(text);
                                        console.log('Parsed data for before:', data);
                                        if (data && data.type) {
                                            console.log('Dispatching paste-from-clipboard BEFORE for block {{ $blockId }}');
                                            $dispatch('paste-from-clipboard', {
                                                clipboardData: text,
                                                targetBlockId: '{{ $blockId }}',
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
                            showContextMenu = false;
                        "
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Paste Before Block') }}">
                    <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                    {{ __('Before') }}
                </button>
                <button
                    @click.stop="
                            console.log('Block Paste AFTER clicked for block {{ $blockId }}');
                            navigator.clipboard.readText().then(text => {
                                console.log('Clipboard read for after, length:', text ? text.length : 0);
                                if (text) {
                                    try {
                                        const data = JSON.parse(text);
                                        console.log('Parsed data for after:', data);
                                        if (data && data.type) {
                                            console.log('Dispatching paste-from-clipboard AFTER for block {{ $blockId }}');
                                            $dispatch('paste-from-clipboard', {
                                                clipboardData: text,
                                                targetBlockId: '{{ $blockId }}',
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
                            showContextMenu = false;
                        "
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Paste After Block') }}">
                    <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                    {{ __('After') }}
                </button>
            </div>
        </div>

        <button
            wire:click="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', replaceBlockId: '{{ $blockId }}' }); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer">
            <x-heroicon-o-arrow-path class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
            <span>{{ __('Replace') }}</span>
        </button>

        <button
            @click="
                showContextMenu = false;
                deleteMessage = '{{ __('Are you sure you want to delete this block?') }}';
                deleteAction = () => {
                    showDeleteModal = false;
                    setTimeout(() => $dispatch('deleteBlock', { blockId: '{{ $blockId }}' }), 100);
                };
                showDeleteModal = true;
                "
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 cursor-pointer">
            <x-heroicon-o-trash class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
            <span>{{ __('Delete') }}</span>
        </button>

        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

        <!-- Move Block (combined Up/Down options) -->
        <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-50 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
            wire:click="$dispatch('moveBlockDown', { blockId: '{{ $blockId }}'}); showContextMenu = false;">
            <div class="flex items-center flex-1">
                <x-heroicon-o-arrows-right-left class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
                <span>{{ __('Move') }}</span>
            </div>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <button
                    wire:click.stop="$dispatch('moveBlockUp', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Move Block Up') }}">
                    <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                    {{ __('Up') }}
                </button>
                <button
                    wire:click.stop="$dispatch('moveBlockDown', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Move Block Down') }}">
                    <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                    {{ __('Down') }}
                </button>
            </div>
        </div>

        <!-- Add Block (combined Before/After options) -->
        <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
            wire:click="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', afterBlockId: '{{ $blockId }}' }); showContextMenu = false;">
            <div class="flex items-center flex-1">
                <x-heroicon-o-plus class="w-4 h-4 ms-0 me-3 text-gray-500 dark:text-gray-400" />
                <span>{{ __('Add Block') }}</span>
            </div>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <button
                    wire:click.stop="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', beforeBlockId: '{{ $blockId }}' }); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Add Block Before') }}">
                    <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                    {{ __('Before') }}
                </button>
                <button
                    wire:click.stop="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', afterBlockId: '{{ $blockId }}' }); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 dark:hover:bg-gray-600 border border-gray-100 dark:border-gray-700 cursor-pointer"
                    title="{{ __('Add Block After') }}">
                    <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                    {{ __('After') }}
                </button>
            </div>
        </div>
        </div>
    </template>

    <!-- Delete Confirmation Modal -->
    @include('page-builder::livewire.builder.partials.delete-confirmation-modal')
</div>
</div>
