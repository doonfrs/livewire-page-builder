<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class FlexibleSizeProperty extends BlockProperty
{
    public array $classes = [];

    public bool $allowCustom = true;

    public string $unit = 'px';

    public function __construct(
        string $name,
        ?string $label = null,
        array $classes = [],
        bool $allowCustom = true,
        string $unit = 'px',
        $defaultValue = null,
    ) {
        parent::__construct($name, $label, $defaultValue);
        $this->classes = $classes;
        $this->allowCustom = $allowCustom;
        $this->unit = $unit;
    }

    public function getType(): string
    {
        return 'flexible-size';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'classes' => $this->classes,
            'allowCustom' => $this->allowCustom,
            'unit' => $this->unit,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Create a new instance of this property
     */
    public static function make(
        string $name,
        ?string $label = null,
        array $classes = [],
        bool $allowCustom = true,
        string $unit = 'px',
        $defaultValue = null
    ): static {
        return new self($name, $label, $classes, $allowCustom, $unit, $defaultValue);
    }
}
