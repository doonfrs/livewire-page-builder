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
    public function setGroup(?string $group, ?string $groupLabel = null, ?int $columns = 1, ?string $groupIcon = null): static
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
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the default value for this property
     */
    public function default($value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Does this field need more than the live edit sheet's default height?
     *
     * The sheet is deliberately short so the page being edited stays visible behind it.
     * A widget that cannot work in that space - an editor carrying its own toolbar -
     * overrides this and the sheet opens tall instead. It changes nothing in the builder's
     * property panel, which has a full sidebar either way.
     */
    public function needsRoom(): bool
    {
        return false;
    }

    abstract public function getType(): string;

    abstract public function toArray(): array;
}
