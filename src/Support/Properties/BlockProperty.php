<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

abstract class BlockProperty
{
    public string $name;
    public string $label;

    public function __construct(string $name, string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? $name;
    }

    abstract public function getType(): string;

    abstract function toArray(): array;
}
