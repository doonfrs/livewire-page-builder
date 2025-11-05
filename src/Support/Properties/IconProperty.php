<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class IconProperty extends BlockProperty
{
    public array $styles = [];

    public array $sets = [];

    public function __construct(
        string $name,
        ?string $label = null,
        ?array $styles = null,
        ?array $sets = null,
        $defaultValue = null,
    ) {
        $this->sets = $sets ?? ['heroicons', 'bootstrap'];
        $this->styles = $styles ?? ['outline', 'solid', 'mini', 'regular', 'fill'];

        parent::__construct($name, $label, $defaultValue);
    }

    public function getType(): string
    {
        return 'icon';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'styles' => $this->styles,
            'sets' => $this->sets,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Set the icon styles
     */
    public function styles(array $styles): static
    {
        $this->styles = $styles;

        return $this;
    }

    /**
     * Set the icon sets
     */
    public function sets(array $sets): static
    {
        $this->sets = $sets;

        return $this;
    }

    /**
     * Create a new instance of this property
     */
    public static function make(
        string $name,
        ?string $label = null,
        ?array $styles = null,
        ?array $sets = null,
        $defaultValue = null
    ): static {
        return new static(
            name: $name,
            label: $label,
            styles: $styles,
            sets: $sets,
            defaultValue: $defaultValue
        );
    }
}
