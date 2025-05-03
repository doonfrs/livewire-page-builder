<div class="{{ $cssClasses }}">
    @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
</div>