<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class ImageProperty extends BlockProperty
{
    public function __construct(
        string $name,
        ?string $label = null,
        $defaultValue = null,
    ) {
        parent::__construct($name, $label, $defaultValue);
    }

    public function getType(): string
    {
        return 'image';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
        ];
    }
}
