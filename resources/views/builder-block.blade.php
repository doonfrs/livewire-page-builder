<div x-data="{ selected: false }" class="{{ $cssClasses }}">
    <div class="block-row relative border transition-all duration-300 ease-in-out "
        :class="selected ? 'border-blue-500' : 'border-gray-300'"
        x-on:block-selected.window="selected = $event.detail.blockId == '{{ $blockId }}'"
        x-on:row-selected.window="selected = false">
        <div class="cursor-pointer" wire:click="blockSelected()">
            <div class="builder-block" style="pointer-events: none">
                @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
            </div>
        </div>
    </div>
</div>
