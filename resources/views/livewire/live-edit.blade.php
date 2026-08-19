<div>
    @teleport('body')
        {{--
            Teleported to <body> on purpose. Both public entry views wrap their content in
            an @container, and container-type: inline-size makes that element the containing
            block for position:fixed descendants - an inline sheet would anchor to the row
            instead of the viewport. Block transforms and filters would do the same.
            Structure is plain Tailwind so the sheet still works in hosts without daisyUI;
            daisyUI is used only for the button and input cosmetics.
        --}}
        <div x-data="{
                show: @entangle('open'),
                restoreScroll() {
                    const y = sessionStorage.getItem('pbLiveEditScroll');
                    if (y === null) return;
                    sessionStorage.removeItem('pbLiveEditScroll');
                    window.scrollTo(0, parseInt(y, 10) || 0);
                },
            }"
            x-init="restoreScroll()"
            x-on:keydown.escape.window="if (show) $wire.close()"
            style="font-size:initial">

            <div x-show="show" x-cloak class="fixed inset-0 z-[100] bg-black/40"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                x-on:click="$wire.close()"></div>

            <div x-show="show" x-cloak
                class="fixed inset-x-0 bottom-0 z-[101] flex max-h-[75dvh] flex-col rounded-t-2xl bg-white shadow-2xl dark:bg-gray-900"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">

                <div class="flex shrink-0 items-center justify-between gap-2 border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <div class="flex min-w-0 items-center gap-2">
                        <x-heroicon-o-adjustments-horizontal class="h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />
                        <h2 class="shrink-0 text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Properties') }}
                        </h2>
                        @if ($blockLabel)
                            <span class="min-w-0 truncate rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $blockLabel }}
                            </span>
                        @endif
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" wire:click="close"
                        title="{{ __('Close') }}">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    @if ($error)
                        <div class="flex flex-col items-center justify-center p-8 text-center text-amber-700 dark:text-amber-400">
                            <x-heroicon-o-exclamation-triangle class="mb-3 h-10 w-10 text-amber-400" />
                            <div class="text-sm">{{ $error }}</div>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($propertyGroups as $groupName => $group)
                                <div class="px-4 py-4 @if ($loop->even) bg-gray-50 dark:bg-gray-800/50 @endif">
                                    <div class="mb-2 flex items-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        <x-dynamic-component :component="$group['icon']" class="me-1 h-4 w-4" />
                                        {{ $group['label'] }}
                                    </div>

                                    <div class="space-y-3 {{ $group['columns'] > 1 ? 'grid grid-cols-' . $group['columns'] . ' gap-2 space-y-0' : '' }}"
                                        wire:key="pb-live-group-{{ $this->fieldKey() }}-{{ $groupName }}">
                                        @foreach ($group['properties'] as $property)
                                            @php($fieldKey = $this->fieldKey() . '-' . $property['name'])
                                            <div wire:key="pb-live-property-{{ $fieldKey }}" class="group">
                                                @include('page-builder::livewire.builder.partials.property-field', [
                                                    'property' => $property,
                                                    'properties' => $properties,
                                                    'rowId' => null,
                                                    'blockId' => null,
                                                    'key' => $fieldKey,
                                                    'updateTarget' => \Trinavo\LivewirePageBuilder\Http\Livewire\LiveEdit::ALIAS,
                                                ])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex shrink-0 items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="close">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="save" wire:loading.attr="disabled"
                        @disabled($error)>
                        <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endteleport
</div>
