<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class SelectProperty extends BlockProperty
{
    public array $options = [];

    public function __construct(
        string $name,
        ?string $label = null,
        array $options = [],
        $defaultValue = null,
    ) {
        parent::__construct($name, $label, $defaultValue);
        $this->options = $options;
    }

    public function getType(): string
    {
        return 'select';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'options' => $this->options,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }
}
