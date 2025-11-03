<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class IconProperty extends BlockProperty
{
    public array $styles = [];

    public function __construct(
        string $name,
        ?string $label = null,
        array $styles = ['outline', 'solid', 'mini'],
        $defaultValue = null,
    ) {
        parent::__construct($name, $label, $defaultValue);
        $this->styles = $styles;
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
        array $styles = ['outline', 'solid', 'mini'],
        $defaultValue = null
    ): static {
        return new self(name: $name, label: $label, styles: $styles, defaultValue: $defaultValue);
    }
}
