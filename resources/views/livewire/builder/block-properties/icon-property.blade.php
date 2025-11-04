<div x-data="{ modalOpen: @entangle('showModal') }">
    <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">
        <span>{{ $propertyLabel }}</span>
    </label>

    <div class="mt-1">
        <!-- Icon preview and select button -->
        <div class="border border-gray-300 rounded bg-white dark:bg-gray-800 dark:border-gray-700 h-20 flex items-center justify-center relative group overflow-hidden">
            @if(!empty($currentValue))
                <x-dynamic-component :component="$currentValue" class="w-12 h-12 text-gray-700 dark:text-gray-300" />
                <button
                    type="button"
                    class="absolute top-1 right-1 hidden group-hover:flex p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none"
                    title="{{ __('Remove icon') }}"
                    wire:click="removeIcon">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            @else
                <x-heroicon-o-swatch class="w-8 h-8 text-gray-400 dark:text-gray-600" />
            @endif
        </div>

        <!-- Select icon button -->
        <button
            type="button"
            @click="modalOpen = true; $wire.call('openModal')"
            class="mt-2 w-full inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-offset-gray-800">
            <x-heroicon-o-squares-2x2 class="w-4 h-4 mr-1" />
            {{ __('Select Icon') }}
        </button>
    </div>

    <!-- Icon Picker Modal -->
    <div x-show="modalOpen"
         x-cloak
         x-data="{
             localSelectedStyle: @entangle('selectedStyle'),
             localSelectedSet: @entangle('selectedSet')
         }"
         class="modal modal-open"
         @click.self="modalOpen = false; $wire.call('closeModal')">
        <div class="modal-box max-w-4xl max-h-[90vh] p-0 flex flex-col" @click.stop>
                <!-- Modal Header -->
                <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Select Icon') }}
                        </h3>
                        <button
                            type="button"
                            @click="modalOpen = false; $wire.call('closeModal')"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Search input -->
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="{{ __('Search icons...') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" />

                    <!-- Filters container -->
                    <div class="flex items-start justify-between gap-4 mt-3">
                        <!-- Style tabs (left) -->
                        @if(count($availableStyles) > 1)
                            <div class="flex-1">
                                <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 block">{{ __('Style') }}</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($availableStyles as $style => $label)
                                        <button
                                            type="button"
                                            @click="localSelectedStyle = '{{ $style }}'; $wire.set('selectedStyle', '{{ $style }}')"
                                            :class="{
                                                'bg-blue-600 text-white dark:bg-blue-700': localSelectedStyle === '{{ $style }}',
                                                'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600': localSelectedStyle !== '{{ $style }}'
                                            }"
                                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Icon Set tabs (right) -->
                        @if(count($availableSets) >= 1)
                            <div class="flex-shrink-0">
                                <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 block">{{ __('Icon Set') }}</label>
                                <div class="flex gap-2">
                                    @foreach($availableSets as $set => $label)
                                        <button
                                            type="button"
                                            @click="localSelectedSet = '{{ $set }}'; $wire.set('selectedSet', '{{ $set }}')"
                                            :class="{
                                                'bg-green-600 text-white dark:bg-green-700': localSelectedSet === '{{ $set }}',
                                                'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600': localSelectedSet !== '{{ $set }}'
                                            }"
                                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Body - Icons Grid -->
                <div class="flex-1 p-4 overflow-y-auto relative" style="min-height: 400px;">
                    <!-- Loading State -->
                    <div wire:loading.delay wire:target="searchQuery,selectedStyle,selectedSet,nextPage,previousPage" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 z-10">
                        <div class="flex flex-col items-center">
                            <div class="inline-block w-12 h-12 border-4 border-base-300 border-t-transparent rounded-full animate-spin mb-4"></div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Loading icons...') }}</p>
                        </div>
                    </div>

                    <!-- Icons Grid -->
                    <div wire:loading.class="invisible" wire:target="searchQuery,selectedStyle,selectedSet,nextPage,previousPage">
                        @if($selectedStyle && isset($icons[$selectedStyle]))
                            @if(count($icons[$selectedStyle]) > 0)
                                <div class="grid grid-cols-6 gap-2">
                                    @foreach($icons[$selectedStyle] as $icon)
                                        <button
                                            type="button"
                                            @click="modalOpen = false; $wire.call('selectIcon', '{{ $icon['component'] }}')"
                                            title="{{ $icon['name'] }}"
                                            class="flex flex-col items-center justify-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-blue-50 hover:border-blue-500 dark:hover:bg-gray-700 dark:hover:border-blue-500 transition-all group
                                                @if($currentValue === $icon['component'])
                                                    bg-blue-100 border-blue-500 dark:bg-gray-700 dark:border-blue-500
                                                @endif">
                                            <x-dynamic-component
                                                :component="$icon['component']"
                                                class="w-8 h-8 text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400" />
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 text-center truncate w-full">
                                                {{ $icon['name'] }}
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <x-heroicon-o-magnifying-glass class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('No icons found') }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Pagination Controls -->
                @if($showModal && $totalPages > 1)
                    <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <!-- Showing info -->
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Showing') }} <span class="font-medium">{{ $showing['from'] }}</span> {{ __('to') }} <span class="font-medium">{{ $showing['to'] }}</span> {{ __('of') }} <span class="font-medium">{{ $showing['total'] }}</span> {{ __('icons') }}
                            </div>

                            <!-- Page controls -->
                            <div class="flex items-center gap-2">
                                <!-- Previous button -->
                                <button
                                    type="button"
                                    wire:click="previousPage"
                                    @if($currentPage === 1) disabled @endif
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 dark:disabled:hover:bg-gray-700">
                                    <x-heroicon-o-chevron-left class="w-4 h-4 mr-1" />
                                    {{ __('Previous') }}
                                </button>

                                <!-- Page indicator -->
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('Page') }} <span class="font-medium">{{ $currentPage }}</span> {{ __('of') }} <span class="font-medium">{{ $totalPages }}</span>
                                </span>

                                <!-- Next button -->
                                <button
                                    type="button"
                                    wire:click="nextPage"
                                    @if($currentPage === $totalPages) disabled @endif
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 dark:disabled:hover:bg-gray-700">
                                    {{ __('Next') }}
                                    <x-heroicon-o-chevron-right class="w-4 h-4 ml-1" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Modal Footer -->
                <div class="flex-shrink-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4">
                    <button
                        type="button"
                        @click="modalOpen = false; $wire.call('closeModal')"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
</div>
