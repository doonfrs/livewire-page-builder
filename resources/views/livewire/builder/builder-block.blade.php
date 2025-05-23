<div id="block-{{ $blockId }}" x-data="{ selected: false, showContextMenu: false, x: 0, y: 0 }"
    class="{{ $cssClasses }} border transition-all duration-300 ease-in-out" style="{{ $inlineStyles }}"
    :class="selected ? 'border-blue-500' : 'border-gray-300'"
    x-on:block-selected.window="selected = $event.detail.blockId == '{{ $blockId }}'"
    x-on:row-selected.window="selected = false"
    @contextmenu.prevent="
        Livewire.dispatch('show-block-context-menu', {
            blockId: '{{ $blockId }}',
            x: $event.clientX,
            y: $event.clientY
        });
        $wire.blockSelected();
    "
    x-on:show-block-context-menu.window="
        if ($event.detail.blockId === '{{ $blockId }}') {
            showContextMenu = true;
            x = $event.detail.x;
            y = $event.detail.y;
        } else {
            showContextMenu = false;
        }
    "
    @click.outside="showContextMenu = false">
    <div class="relative">
        <div class="cursor-pointer" wire:click="blockSelected()">
            <div class="builder-block relative">
                @if (!$classExists)
                <div class="text-red-500">{{ __('Unknown block') }}: {{ $blockAlias }}</div>
                @else
                @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
                @endif
                <div class="absolute inset-0 z-50" style="pointer-events: all;"></div>
            </div>
        </div>
        <!-- Context Menu UI -->
        <div x-show="showContextMenu" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" :style="`position: fixed; left: ${x}px; top: ${y}px;`"
            class="context-menu bg-white border border-gray-200 rounded-lg shadow-lg py-2 w-[250px] z-52">
            
            <div class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700 mb-1">
                {{ __('Block Actions') }}
            </div>
            
            <button wire:click="blockSelected(); showContextMenu = false;"
                class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-50">
                <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 ms-0 me-3 text-gray-500" />
                <span>{{ __('Select') }}</span>
            </button>
            <button wire:click="copyBlock(); showContextMenu = false;"
                class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-50">
                <x-heroicon-o-clipboard-document class="w-4 h-4 ms-0 me-3 text-gray-500" />
                <span>{{ __('Copy') }}</span>
            </button>

            <!-- Paste Block (combined before/after options) -->
            <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 border-b border-gray-50">
                <div class="flex items-center flex-1">
                    <x-heroicon-o-clipboard-document-check class="w-4 h-4 ms-0 me-3 text-gray-500" />
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
                                                targetBlockId: '{{ $blockId }}',
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
                            showContextMenu = false;
                        "
                        class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                        title="{{ __('Paste Before Block') }}">
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
                                                targetBlockId: '{{ $blockId }}',
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
                            showContextMenu = false;
                        "
                        class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                        title="{{ __('Paste After Block') }}">
                        <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                        {{ __('After') }}
                    </button>
                </div>
            </div>

            <button
                @click="
                showContextMenu = false;
                confirm('{{ __('Are you sure you want to delete this block?') }}') 
                && $dispatch('deleteBlock', { blockId: '{{ $blockId }}'}); 
                "
                class="flex items-center w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 border-b border-gray-50">
                <x-heroicon-o-trash class="w-4 h-4 ms-0 me-3 text-red-500" />
                <span>{{ __('Delete') }}</span>
            </button>
            
            <div class="border-t border-gray-200 my-1"></div>
            
            <!-- Move Block (combined Up/Down options) -->
            <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 border-b border-gray-50">
                <div class="flex items-center flex-1">
                    <x-heroicon-o-arrows-up-down class="w-4 h-4 ms-0 me-3 text-gray-500" />
                    <span>{{ __('Move') }}</span>
                </div>
                <div class="flex space-x-2 rtl:space-x-reverse">
                    <button wire:click="$dispatch('moveBlockUp', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                        class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                        title="{{ __('Move Block Up') }}">
                        <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                        {{ __('Up') }}
                    </button>
                    <button wire:click="$dispatch('moveBlockDown', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                        class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                        title="{{ __('Move Block Down') }}">
                        <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                        {{ __('Down') }}
                    </button>
                </div>
            </div>
            
            <!-- Add Block (combined Before/After options) -->
            <div class="flex items-center w-full px-4 py-2 text-sm text-green-600">
                <div class="flex items-center flex-1">
                    <x-heroicon-o-plus class="w-4 h-4 ms-0 me-3 text-green-500" />
                    <span>{{ __('Add Block') }}</span>
                </div>
                <div class="flex space-x-2 rtl:space-x-reverse">
                    <button wire:click="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', beforeBlockId: '{{ $blockId }}' }); showContextMenu = false;"
                        class="px-2 py-1 text-xs rounded hover:bg-green-50 border border-green-100"
                        title="{{ __('Add Block Before') }}">
                        <x-heroicon-o-arrow-up class="w-3 h-3 inline-block" />
                        {{ __('Before') }}
                    </button>
                    <button wire:click="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', afterBlockId: '{{ $blockId }}' }); showContextMenu = false;"
                        class="px-2 py-1 text-xs rounded hover:bg-green-50 border border-green-100"
                        title="{{ __('Add Block After') }}">
                        <x-heroicon-o-arrow-down class="w-3 h-3 inline-block" />
                        {{ __('After') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>