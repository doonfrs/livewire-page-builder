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
                show: $wire.entangle('open'),
                restoreScroll() {
                    const y = sessionStorage.getItem('pbLiveEditScroll');
                    if (y === null) return;
                    sessionStorage.removeItem('pbLiveEditScroll');
                    window.scrollTo(0, parseInt(y, 10) || 0);
                },
                /*
                 * Paint an edit straight onto the page.
                 *
                 * The block is its own Livewire component, so the sheet cannot reach it from
                 * PHP. The server sends the wrapper's id and the values to set; we find that
                 * component and set them on it, which re-renders that block and nothing else.
                 * Only the last set is `live`, so a change touching several properties costs
                 * one request rather than one per property.
                 */
                applyPreview(detail) {
                    const wrapper = document.getElementById(detail?.target);
                    if (! wrapper) return;

                    const el = wrapper.querySelector('[wire\\:id]');
                    if (! el) return;

                    const block = window.Livewire?.find(el.getAttribute('wire:id'));
                    if (! block) return;

                    const entries = Object.entries(detail.props ?? {});
                    entries.forEach(([name, value], i) => {
                        block.$set(name, value, i === entries.length - 1);
                    });
                },
            }"
            x-init="restoreScroll()"
            x-on:pb-live-preview.window="applyPreview($event.detail)"
            {{-- Escape is the keyboard spelling of Cancel, so it reverts the same way. --}}
            x-on:keydown.escape.window="if (show) $wire.close()"
            style="font-size:initial">

            {{-- Backdrop dims the page but does not dismiss: edits are already live on the page
                 behind it, and losing them to a stray click would be expensive. Leaving is a
                 deliberate choice between Cancel and Save. --}}
            <div x-show="show" x-cloak class="fixed inset-0 z-[100] bg-black/40"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

            {{-- Full width only while there is no room to spare: past 28rem the sheet is capped and
                 centred, so a handful of property fields never stretch across a desktop screen. --}}
            <div x-show="show" x-cloak
                class="pb-live-compact fixed inset-x-0 bottom-0 z-[101] mx-auto flex max-h-[40dvh] w-full max-w-md flex-col rounded-t-2xl bg-white shadow-2xl dark:bg-gray-900"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">

                <div class="flex shrink-0 items-center justify-between gap-2 border-b border-gray-200 px-3 py-2 dark:border-gray-700">
                    <div class="flex min-w-0 items-center gap-1.5">
                        <x-heroicon-o-adjustments-horizontal class="h-4 w-4 shrink-0 text-gray-500 dark:text-gray-400" />
                        <h2 class="shrink-0 text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Properties') }}
                        </h2>
                        @if ($blockLabel)
                            <span class="min-w-0 truncate rounded bg-gray-100 px-1.5 py-0.5 text-[0.7rem] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $blockLabel }}
                            </span>
                        @endif
                    </div>
                    <button type="button" class="btn btn-ghost btn-xs btn-circle" wire:click="close"
                        title="{{ __('Close') }}">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    @if ($error)
                        <div class="flex flex-col items-center justify-center p-6 text-center text-amber-700 dark:text-amber-400">
                            <x-heroicon-o-exclamation-triangle class="mb-2 h-8 w-8 text-amber-400" />
                            <div class="text-xs">{{ $error }}</div>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($propertyGroups as $groupName => $group)
                                <div class="px-3 py-3 @if ($loop->even) bg-gray-50 dark:bg-gray-800/50 @endif">
                                    <div class="mb-1.5 flex items-center text-[0.65rem] font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        <x-dynamic-component :component="$group['icon']" class="me-1 h-3.5 w-3.5" />
                                        {{ $group['label'] }}
                                    </div>

                                    <div class="space-y-2 {{ $group['columns'] > 1 ? 'grid grid-cols-' . $group['columns'] . ' gap-2 space-y-0' : '' }}"
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

                {{-- Opposite ends, never adjacent: Cancel throws away work that is already visible
                     on the page, so it must not sit under a thumb aiming for Save. Cancel takes the
                     start edge and Save the end edge, which mirrors itself in RTL. --}}
                <div class="flex shrink-0 items-center justify-between gap-2 border-t border-gray-200 bg-gray-100 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" class="btn btn-ghost btn-xs" wire:click="close">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary btn-xs" wire:click="save" wire:loading.attr="disabled"
                        @disabled($error)>
                        <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endteleport

    {{-- The property widgets are shared with the builder's panel, which sizes them for a
         desktop sidebar. On a sheet that sits over the page they need to be smaller. --}}
    <style>
        .pb-live-compact label,
        .pb-live-compact input,
        .pb-live-compact select,
        .pb-live-compact textarea,
        .pb-live-compact button {
            font-size: 0.75rem;
        }

        .pb-live-compact input,
        .pb-live-compact select,
        .pb-live-compact textarea {
            padding-top: 0.3rem;
            padding-bottom: 0.3rem;
        }
    </style>
</div>
