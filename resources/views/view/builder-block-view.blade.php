<div class="{{ $cssClasses }}" @if(!empty($inlineStyles))style="{{ $inlineStyles }}"@endif>
    @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
</div>