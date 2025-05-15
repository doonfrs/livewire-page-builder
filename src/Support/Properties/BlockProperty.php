<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

abstract class BlockProperty
{
    public string $name;

    public string $label;

    public $defaultValue = null;

    public ?string $group = null;

    public ?string $groupLabel = null;

    public ?string $groupIcon = null;

    public ?int $groupColumns = null;

    public function __construct(
        string $name,
        ?string $label = null,
        $defaultValue = null
    ) {
        $this->name = $name;
        $this->label = $label ?? $name;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Set the property group information
     */
    public function setGroup(?string $group, ?string $groupLabel = null, ?int $columns = 1, ?string $groupIcon = null): self
    {
        $this->group = $group;
        $this->groupLabel = $groupLabel ?? ucfirst($group);
        $this->groupColumns = $columns;
        $this->groupIcon = $groupIcon;

        return $this;
    }

    /**
     * Set the label for this property
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    abstract public function getType(): string;

    abstract public function toArray(): array;
}
