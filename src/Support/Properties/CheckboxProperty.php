<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class CheckboxProperty extends BlockProperty
{
    public function getType(): string
    {
        return 'checkbox';
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
