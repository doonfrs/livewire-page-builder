<div id="block-{{ $blockId }}" x-data="{ selected: false, showContextMenu: false, x: 0, y: 0 }"
    class="{{ $cssClasses }} border transition-all duration-300 ease-in-out" style="{{ $inlineStyles }}"
    :class="selected ? 'border-blue-500' : 'border-gray-300'"
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
            showContextMenu = true;
            x = $event.detail.x;
            y = $event.detail.y;
        } else {
            showContextMenu = false;
        }
    " @click.outside="showContextMenu = false" > @endif
    <div class="relative">
    @if ($isRowBlock)
        <!-- For RowBlocks, add minimal click handling that doesn't interfere with inner blocks -->
        <div class="builder-block relative" @click.self="$wire.blockSelected()">
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
    <div x-show="showContextMenu" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95" :style="`position: fixed; left: ${x}px; top: ${y}px;`"
        class="context-menu bg-white border border-gray-200 rounded-lg shadow-lg py-2 w-[250px] z-52">

        <div
            class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700 mb-1">
            {{ __('Block Actions') }}
        </div>

        <button wire:click="blockSelected(); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-50">
            <svg class="w-4 h-4 ms-0 me-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
            </svg>
            <span>{{ __('Select') }}</span>
        </button>
        <button wire:click="copyBlock(); showContextMenu = false;"
            class="flex items-center w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-50">
            <svg class="w-4 h-4 ms-0 me-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z" />
            </svg>
            <span>{{ __('Copy') }}</span>
        </button>

        <!-- Paste Block (combined before/after options) -->
        <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 border-b border-gray-50">
            <div class="flex items-center flex-1">
                <svg class="w-4 h-4 ms-0 me-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
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
                                                targetBlockId: '{{ $blockId }}',
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
                            showContextMenu = false;
                        "
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                    title="{{ __('Paste Before Block') }}">
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
                                                targetBlockId: '{{ $blockId }}',
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
                            showContextMenu = false;
                        "
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                    title="{{ __('Paste After Block') }}">
                    <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                    </svg>
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
            <svg class="w-4 h-4 ms-0 me-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
            <span>{{ __('Delete') }}</span>
        </button>

        <div class="border-t border-gray-200 my-1"></div>

        <!-- Move Block (combined Up/Down options) -->
        <div class="flex items-center w-full px-4 py-2 text-sm text-gray-700 border-b border-gray-50">
            <div class="flex items-center flex-1">
                <svg class="w-4 h-4 ms-0 me-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>{{ __('Move') }}</span>
            </div>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <button
                    wire:click="$dispatch('moveBlockUp', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                    title="{{ __('Move Block Up') }}">
                    <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" />
                    </svg>
                    {{ __('Up') }}
                </button>
                <button
                    wire:click="$dispatch('moveBlockDown', { blockId: '{{ $blockId }}'}); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-gray-200 border border-gray-100"
                    title="{{ __('Move Block Down') }}">
                    <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                    </svg>
                    {{ __('Down') }}
                </button>
            </div>
        </div>

        <!-- Add Block (combined Before/After options) -->
        <div class="flex items-center w-full px-4 py-2 text-sm text-green-600">
            <div class="flex items-center flex-1">
                <svg class="w-4 h-4 ms-0 me-3 text-green-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>{{ __('Add Block') }}</span>
            </div>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <button
                    wire:click="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', beforeBlockId: '{{ $blockId }}' }); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-green-50 border border-green-100"
                    title="{{ __('Add Block Before') }}">
                    <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75" />
                    </svg>
                    {{ __('Before') }}
                </button>
                <button
                    wire:click="$dispatch('openBlockModal', { rowId: '{{ $rowId }}', afterBlockId: '{{ $blockId }}' }); showContextMenu = false;"
                    class="px-2 py-1 text-xs rounded hover:bg-green-50 border border-green-100"
                    title="{{ __('Add Block After') }}">
                    <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75" />
                    </svg>
                    {{ __('After') }}
                </button>
            </div>
        </div>
    </div>
</div>
</div>
