<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class CustomProperty extends BlockProperty
{
    public ?string $component = null;

    public array $config = [];

    public function __construct(
        string $name,
        ?string $label = null,
        ?string $component = null,
        ?array $config = null,
        $defaultValue = null,
    ) {
        $this->component = $component;
        $this->config = $config ?? [];

        parent::__construct($name, $label, $defaultValue);
    }

    public function getType(): string
    {
        return 'custom';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'component' => $this->component,
            'config' => $this->config,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Set the Livewire component class to render
     */
    public function component(string $component): static
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Set configuration data to pass to the component
     */
    public function config(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Create a new instance of this property
     */
    public static function make(
        string $name,
        ?string $label = null,
        ?string $component = null,
        ?array $config = null,
        $defaultValue = null
    ): static {
        return new static(
            name: $name,
            label: $label,
            component: $component,
            config: $config,
            defaultValue: $defaultValue
        );
    }
}
