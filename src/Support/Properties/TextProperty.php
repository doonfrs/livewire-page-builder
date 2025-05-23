<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class TextProperty extends BlockProperty
{
    public bool $numeric = false;

    public ?int $min = null;

    public ?int $max = null;

    public function __construct(
        string $name,
        ?string $label = null,
        bool $numeric = false,
        $defaultValue = null,
        $min = null,
        $max = null
    ) {
        parent::__construct($name, $label, $defaultValue);
        $this->numeric = $numeric;
        $this->min = $min;
        $this->max = $max;
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
            'min' => $this->min,
            'max' => $this->max,
            'numeric' => $this->numeric,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Create a new instance of this property
     */
    public static function make(string $name, ?string $label = null, bool $numeric = false, $defaultValue = null, $min = null, $max = null): self
    {
        return new self($name, $label, $numeric, $defaultValue, $min, $max);
    }
}
