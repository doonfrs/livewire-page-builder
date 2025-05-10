<div class="{{ $cssClasses }} items-center" style="{{ $inlineStyles }}">
    @if(!$classExists)
        <div class="text-red-500">Unknown block: {{ $blockAlias }}</div>
    @else
        @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
    @endif
</div>