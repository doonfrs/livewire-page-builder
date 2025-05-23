<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class ColorProperty extends BlockProperty
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
        return 'color';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Create a new instance of this property
     */
    public static function make(string $name, ?string $label = null, $defaultValue = null): self
    {
        return new self($name, $label, $defaultValue);
    }
}
