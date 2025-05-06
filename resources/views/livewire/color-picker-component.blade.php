<div>
    <div x-data="{ 
        isOpen: false,
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
        adjustPosition() {
            try {
                // Find the button element more reliably
                const buttonEl = this.$root.querySelector('.color-picker-button');
                if (!buttonEl) {
                    // Default to left-0 if button not found
                    this.popoverPosition = 'left-0';
                    return;
                }
                
                const rect = buttonEl.getBoundingClientRect();
                const windowWidth = window.innerWidth;
                
                // If we're close to the right edge or in RTL mode
                if ((windowWidth - rect.right) < 200 && document.dir === 'rtl') {
                    this.popoverPosition = 'right-0';
                } else {
                    this.popoverPosition = 'left-0';
                }
            } catch (error) {
                // Log error and use default position
                console.error('Error calculating popover position:', error);
                this.popoverPosition = 'left-0';
            }
        },
        popoverPosition: 'left-0',
        togglePopover() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                $wire.showModal = true;
                // Calculate positioning after the DOM has updated
                setTimeout(() => {
                    this.adjustPosition();
                }, 50);
            }
        },
        closePopover() {
            this.isOpen = false;
            $wire.showModal = false;
        }
    }" class="relative">
        <!-- Color Display and Button -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="togglePopover()"
                class="color-picker-button flex items-center gap-2 p-2 border border-gray-300 bg-white rounded w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                <div class="w-5 h-5 rounded border border-gray-300 dark:border-gray-600 overflow-hidden"
                    :class="getColorClass('{{ $currentValue }}')"
                    :style="getColorStyle('{{ $currentValue }}')">
                </div>
                <span x-text="getColorName('{{ $currentValue }}')"></span>
                <x-heroicon-o-chevron-down class="w-4 h-4 ml-auto text-gray-500 dark:text-gray-400" />
            </button>
        </div>

        <!-- Popover - Positioned above the input -->
        <div x-cloak x-show="isOpen" 
            @keydown.escape.window="closePopover()"
            @click.outside="closePopover()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :class="['absolute bottom-full mb-2 w-64 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5', popoverPosition]">
            
            <!-- Popover Header -->
            <div class="flex items-center justify-between p-2 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xs font-medium text-gray-900 dark:text-gray-100">Select Color</h3>
                <button @click="closePopover()" type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <span class="sr-only">Close</span>
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                </button>
            </div>

            <div class="p-2 max-h-[300px] overflow-y-auto">
                <!-- All Colors in a flat grid -->
                <div class="mb-3">
                    <!-- Flat color grid -->
                    <div class="grid grid-cols-8 gap-1 mb-2">
                        @foreach ($presetColors['gray'] as $color)
                        <button
                            wire:click="selectColor('{{ $color }}')"
                            class="w-5 h-5 rounded-sm border border-gray-300 dark:border-gray-600 transition-all hover:scale-110 hover:shadow bg-{{ $color }}"
                            title="{{ ucwords(str_replace('-', ' ', $color)) }}">
                        </button>
                        @endforeach
                    </div>
                    
                    <div class="grid grid-cols-8 gap-1">
                        @foreach (['red', 'blue', 'green', 'yellow', 'pink', 'purple', 'indigo'] as $colorGroup)
                            @foreach ($presetColors[$colorGroup] as $color)
                            <button
                                wire:click="selectColor('{{ $color }}')"
                                class="w-5 h-5 rounded-sm border border-gray-300 dark:border-gray-600 transition-all hover:scale-110 hover:shadow bg-{{ $color }}"
                                title="{{ ucwords(str_replace('-', ' ', $color)) }}">
                            </button>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <!-- Custom Color Picker -->
                <div class="mb-2">
                    <div class="flex gap-1 items-center">
                        <input
                            type="color"
                            wire:model.live="customColor"
                            class="cursor-pointer h-7 w-7 border-0 p-0" />
                        <input
                            type="text"
                            wire:model.live="customColor"
                            placeholder="Hex color"
                            class="flex-1 p-1 text-xs border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                        <button
                            type="button"
                            wire:click="selectCustomColor"
                            class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors">
                            Apply
                        </button>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        wire:click="clearColor"
                        class="rounded bg-white dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Clear
                    </button>
                    <button
                        type="button"
                        @click="closePopover()"
                        class="rounded bg-gray-200 dark:bg-gray-600 px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-200 shadow-sm hover:bg-gray-300 dark:hover:bg-gray-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>