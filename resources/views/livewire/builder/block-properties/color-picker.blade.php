<div>
    {{--
        The palette is teleported to <body> and positioned against the viewport rather than
        against this wrapper. Both hosts clip an absolutely positioned popover: the builder
        sidebar is an overflow-hidden <aside> around an overflow-y-auto panel, and the live
        edit sheet is a max-h-[40dvh] bottom sheet with its own scroll container. Same
        pattern as the row / block context menus.
    --}}
    <div x-data="{
        isOpen: false,
        currentValue: $wire.entangle('currentValue'),
        customColor: $wire.entangle('customColor'),
        opacity: $wire.entangle('opacity'),
        activeTab: $wire.entangle('activeTab'),
        pos: { left: 0, width: 256, maxHeight: 400, top: null, bottom: 0 },
        getColorName(color) {
            if (!color) return 'None';
            return color.includes('#') ? color : color.replace('-', ' ').replace(/^\w/, c => c.toUpperCase());
        },
        getColorClass(color) {
            if (!color) return '';
            return color.includes('#') ? '' : `bg-${color}`;
        },
        getColorStyle(color) {
            if (!color || !color.includes('#')) return '';
            return `background-color: ${color};`;
        },
        /*
         * Anchor the palette to the trigger, then keep it on screen: clamped on the
         * horizontal axis, flipped to whichever side has more room on the vertical one,
         * and capped so a short viewport scrolls the swatches instead of pushing them
         * past an edge.
         */
        positionPopover() {
            const trigger = this.$refs.trigger;
            if (!trigger) return;

            const rect = trigger.getBoundingClientRect();
            const margin = 8;
            const gap = 8;

            // Read the direction off the element, not document.dir: the dir attribute can
            // sit on any ancestor, and the storefront sheet is teleported out of the page.
            const rtl = getComputedStyle(this.$root).direction === 'rtl';

            const width = Math.min(256, window.innerWidth - margin * 2);
            let left = rtl ? rect.right - width : rect.left;
            left = Math.max(margin, Math.min(left, window.innerWidth - width - margin));

            const spaceAbove = rect.top - gap - margin;
            const spaceBelow = window.innerHeight - rect.bottom - gap - margin;
            const above = spaceAbove >= 240 || spaceAbove >= spaceBelow;
            const space = Math.max(160, above ? spaceAbove : spaceBelow);

            // Anchored by the edge nearest the trigger so the panel grows away from it and
            // needs no height measurement before the first paint.
            this.pos = {
                left,
                width,
                maxHeight: Math.min(400, space),
                top: above ? null : rect.bottom + gap,
                bottom: above ? window.innerHeight - rect.top + gap : null,
            };
        },
        popoverStyle() {
            const edge = this.pos.top !== null ? `top: ${this.pos.top}px;` : `bottom: ${this.pos.bottom}px;`;
            return `left: ${this.pos.left}px; width: ${this.pos.width}px; max-height: ${this.pos.maxHeight}px; ${edge}`;
        },
        togglePopover() {
            if (this.isOpen) {
                this.closePopover();
                return;
            }
            this.positionPopover();
            this.isOpen = true;
            $wire.showModal = true;
        },
        closePopover() {
            this.isOpen = false;
            $wire.showModal = false;
        },
        customColorDebounce: null,
        applyCustomColorWithDebounce(color) {
            if (this.customColorDebounce) {
                clearTimeout(this.customColorDebounce);
            }
            this.customColorDebounce = setTimeout(() => {
                $wire.selectCustomColor();
            }, 500);
        }
    }" class="relative"
        @resize.window="isOpen && positionPopover()"
        {{-- Capture phase on purpose: scroll does not bubble, so a plain .window listener
             would miss the properties panel scrolling under the open palette. --}}
        @scroll.window.capture="isOpen && positionPopover()">
        <!-- Color Display and Button -->
        <div class="flex items-center gap-2">
            <button type="button" x-ref="trigger" @click="togglePopover()"
                class="color-picker-button flex items-center gap-2 p-2 border border-gray-300 bg-white rounded w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                <div class="w-6 h-6 shrink-0 rounded border border-gray-300 dark:border-gray-600 overflow-hidden"
                    :class="getColorClass(currentValue)" :style="getColorStyle(currentValue)">
                </div>
                <span class="text-xs font-mono truncate flex-1 text-left text-gray-600 dark:text-gray-400"
                    x-text="currentValue || 'None'"></span>
                <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 text-gray-500 dark:text-gray-400" />
            </button>
        </div>

        <!-- Popover - anchored to the trigger, kept inside the viewport -->
        <template x-teleport="body">
            <div x-cloak x-show="isOpen" @keydown.escape.window="closePopover()" @click.outside="closePopover()"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                :style="popoverStyle()"
                class="fixed z-[9999] flex flex-col rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black/5">

                <!-- Popover Header -->
                <div class="flex shrink-0 items-center justify-between p-2 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ __('Select Color') }}</h3>
                    <button @click="closePopover()" type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <span class="sr-only">{{ __('Close') }}</span>
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex shrink-0 border-b border-gray-200 dark:border-gray-700">
                    <button @click="activeTab = 'theme'; $wire.setTab('theme')"
                        :class="{ 'bg-gray-100 dark:bg-gray-700 border-b-2 border-blue-500': activeTab === 'theme' }"
                        class="flex-1 py-2 text-xs font-medium text-center">
                        {{ __('Theme Colors') }}
                    </button>
                    <button @click="activeTab = 'tailwind'; $wire.setTab('tailwind')"
                        :class="{ 'bg-gray-100 dark:bg-gray-700 border-b-2 border-blue-500': activeTab === 'tailwind' }"
                        class="flex-1 py-2 text-xs font-medium text-center">
                        {{ __('Tailwind') }}
                    </button>
                    <button @click="activeTab = 'custom'; $wire.setTab('custom')"
                        :class="{ 'bg-gray-100 dark:bg-gray-700 border-b-2 border-blue-500': activeTab === 'custom' }"
                        class="flex-1 py-2 text-xs font-medium text-center">
                        {{ __('Custom') }}
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-2">
                    <!-- Theme Colors Tab -->
                    <div x-show="activeTab === 'theme'">
                        @foreach ($themeColors as $colorGroup => $colors)
                            <div class="mb-2">
                                <h4 class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ __(ucfirst($colorGroup)) }}</h4>
                                <div class="grid grid-cols-4 gap-1">
                                    @foreach ($colors as $color)
                                        <button wire:click="selectColor('{{ $color }}'); $dispatch('color-selected')"
                                            @color-selected.window="closePopover()"
                                            class="flex flex-col items-center p-1 rounded border border-gray-200 dark:border-gray-700 transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <div
                                                class="w-full h-5 rounded-sm border border-gray-300 dark:border-gray-600 bg-{{ $color }}">
                                            </div>
                                            <span class="text-[10px] mt-1 truncate">{{ $color }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Tailwind Colors Tab -->
                    <div x-show="activeTab === 'tailwind'">
                        <!-- Gray Colors -->
                        <div class="mb-2">
                            <h4 class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Gray') }}</h4>
                            <div class="grid grid-cols-5 gap-1">
                                @foreach ($presetColors['gray'] as $color)
                                    <button wire:click="selectColor('{{ $color }}'); $dispatch('color-selected')"
                                        @color-selected.window="closePopover()"
                                        class="w-full h-10 rounded-sm border border-gray-300 dark:border-gray-600 transition-all hover:scale-105 hover:shadow bg-{{ $color }} flex items-end justify-center pb-1"
                                        title="{{ ucwords(str_replace('-', ' ', $color)) }}">
                                        <span
                                            class="text-[9px] text-white text-shadow">{{ explode('-', $color)[1] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Other Color Groups -->
                        @foreach (['red', 'blue', 'green', 'yellow', 'pink', 'purple', 'indigo'] as $colorGroup)
                            <div class="mb-2">
                                <h4 class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    {{ __(ucfirst($colorGroup)) }}</h4>
                                <div class="grid grid-cols-5 gap-1">
                                    @foreach ($presetColors[$colorGroup] as $color)
                                        <button
                                            wire:click="selectColor('{{ $color }}'); $dispatch('color-selected')"
                                            @color-selected.window="closePopover()"
                                            class="w-full h-10 rounded-sm border border-gray-300 dark:border-gray-600 transition-all hover:scale-105 hover:shadow bg-{{ $color }} flex items-end justify-center pb-1"
                                            title="{{ ucwords(str_replace('-', ' ', $color)) }}">
                                            <span
                                                class="text-[9px] text-white text-shadow">{{ explode('-', $color)[1] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Custom Color Picker Tab -->
                    <div x-show="activeTab === 'custom'">
                        <div class="flex gap-1 items-center mb-3">
                            <input type="color" wire:model.live="customColor"
                                @input="applyCustomColorWithDebounce($event.target.value)"
                                class="cursor-pointer h-10 w-10 border-0 p-0" />
                            <input type="text" wire:model.live="customColor"
                                @input="applyCustomColorWithDebounce($event.target.value)" placeholder="Hex color"
                                class="flex-1 p-2 text-sm border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                        </div>

                        <!-- Opacity Slider -->
                        <div class="mb-3">
                            <label class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                <span>{{ __('Opacity') }}</span>
                                <span class="font-medium" x-text="`${opacity}%`"></span>
                            </label>
                            <input type="range" wire:model.live="opacity"
                                @input="applyCustomColorWithDebounce($event.target.value)"
                                min="0" max="100" step="1"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700" />
                        </div>

                        <!-- Color Preview with Opacity -->
                        <div class="relative h-10 w-full rounded border border-gray-300 dark:border-gray-600 mb-2 overflow-hidden">
                            <!-- Checkerboard background to show transparency -->
                            <div class="absolute inset-0 bg-[linear-gradient(45deg,#ccc_25%,transparent_25%),linear-gradient(-45deg,#ccc_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#ccc_75%),linear-gradient(-45deg,transparent_75%,#ccc_75%)] bg-[length:20px_20px] bg-[position:0_0,0_10px,10px_-10px,-10px_0px]"></div>
                            <div class="absolute inset-0"
                                :style="`background-color: ${customColor}; opacity: ${opacity / 100};`"></div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions - outside the scroll area so they stay reachable -->
                <div class="flex shrink-0 justify-between border-t border-gray-200 p-2 dark:border-gray-700">
                    <button type="button" wire:click="clearColor" @click="closePopover()"
                        class="rounded bg-white dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ __('Clear') }}
                    </button>
                    <button type="button" @click="closePopover()"
                        class="rounded bg-gray-200 dark:bg-gray-600 px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-200 shadow-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
