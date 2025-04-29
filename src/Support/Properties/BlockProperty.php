<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

abstract class BlockProperty
{
    public string $name;
    public string $label;
    public $defaultValue = null;

    public function __construct(
        string $name,
        string $label = null,
        $defaultValue = null
    ) {
        $this->name = $name;
        $this->label = $label ?? $name;
        $this->defaultValue = $defaultValue;
    }

    abstract public function getType(): string;

    abstract function toArray(): array;
}
