<div x-data="{ selected: false }">
    <div class="block-row relative border " :class="selected ? 'border-blue-500' : 'border-gray-300'"
        x-on:block-selected.window="selected = $event.detail.blockId == '{{ $blockId }}'"
        x-on:row-selected.window="selected = false">
        <div class="cursor-pointer grid grid-cols-12" wire:click="blockSelected()">
            <div class="builder-block {{ $cssClasses }}" style="pointer-events: none">
                @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
            </div>
        </div>
    </div>
</div>
