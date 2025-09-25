<div class="space-y-3" wire:loading.class="opacity-60 pointer-events-none" wire:target="mode,activeDevice">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $property['label'] }}</span>
        <div class="flex items-center space-x-1 text-xs">
            <button type="button"
                class="px-2 py-1 rounded border transition-colors {{ $mode === 'per-device' ? 'bg-gray-900 text-white border-gray-900 dark:bg-gray-100 dark:text-gray-900' : 'text-gray-600 border-transparent hover:border-gray-300 dark:text-gray-400' }}"
                wire:click="$set('mode', 'per-device')">
                {{ __('Per Device') }}
            </button>
            <button type="button"
                class="px-2 py-1 rounded border transition-colors {{ $mode === 'all' ? 'bg-gray-900 text-white border-gray-900 dark:bg-gray-100 dark:text-gray-900' : 'text-gray-600 border-transparent hover:border-gray-300 dark:text-gray-400' }}"
                wire:click="$set('mode', 'all')">
                {{ __('All') }}
            </button>
        </div>
    </div>

    @if ($mode === 'per-device')
        <div class="flex items-center space-x-2 text-xs">
            @foreach ($property['devices'] as $deviceKey => $device)
                <button type="button"
                    class="flex items-center px-2 py-1 rounded border transition-colors {{ $activeDevice === $deviceKey ? 'bg-gray-100 border-gray-300 text-gray-800 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700' : 'border-transparent text-gray-500 hover:border-gray-200 dark:text-gray-400' }}"
                    wire:click="$set('activeDevice', '{{ $deviceKey }}')">
                    @if (!empty($device['icon']))
                        <x-dynamic-component :component="$device['icon']" class="w-4 h-4 mr-1" />
                    @endif
                    <span>{{ $device['label'] }}</span>
                </button>
            @endforeach
        </div>
    @endif

    @if ($mode === 'all')
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-3 text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ __('All Devices') }}
            </div>

            <div class="grid grid-cols-2 gap-3">
                @foreach ($property['directions'] as $directionKey => $directionLabel)
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                            {{ $directionLabel }}
                        </label>
                        <input type="text"
                            class="w-full rounded border border-gray-300 p-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            wire:key="responsive-{{ $property['name'] }}-all-{{ $directionKey }}"
                            wire:model.live.debounce.400ms="values.all.{{ $directionKey }}"
                            placeholder="-" />
                    </div>
                @endforeach
            </div>
        </div>
    @else
        @foreach ($property['devices'] as $deviceKey => $device)
            <div wire:key="responsive-{{ $property['name'] }}-{{ $deviceKey }}"
                class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900 {{ $activeDevice === $deviceKey ? '' : 'hidden' }}">
                <div class="mb-3 text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
                    {{ $device['label'] }}
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @foreach ($property['directions'] as $directionKey => $directionLabel)
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                                {{ $directionLabel }}
                            </label>
                            <input type="text"
                                class="w-full rounded border border-gray-300 p-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                wire:key="responsive-{{ $property['name'] }}-{{ $deviceKey }}-{{ $directionKey }}"
                                wire:model.live.debounce.400ms="values.{{ $deviceKey }}.{{ $directionKey }}"
                                placeholder="-" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
