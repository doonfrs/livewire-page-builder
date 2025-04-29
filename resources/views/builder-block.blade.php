<div x-data="{ selected: false }">
    <div
        class="block-row relative border "
        :class="selected ? 'border-blue-500' : 'border-gray-300'"
        x-on:block-selected.window="selected = $event.detail.blockId == '{{$blockId}}'">
        <div
            class="cursor-pointer"
            wire:click="blockSelected()">
            <div class="builder-block" style="pointer-events: none">
                @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
            </div>
        </div>
    </div>
</div>