<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class TextProperty extends BlockProperty
{
    public bool $numeric = false;

    public function __construct(
        string $name,
        string $label = null,
        bool $numeric = false,
        $defaultValue = null
    ) {
        parent::__construct($name, $label, $defaultValue);
        $this->numeric = $numeric;
    }

    public function getType(): string
    {
        return 'text';
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
