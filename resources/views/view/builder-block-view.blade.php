<div class="{{ $cssClasses }}" @if(!empty($inlineStyles))style="{{ $inlineStyles }}"@endif>
    @if(!$classExists)
        <div class="text-red-500">Unknown block: {{ $blockAlias }}</div>
    @else
        @livewire($blockAlias, $properties, key($blockId . '-' . md5(json_encode($properties))))
    @endif
</div>