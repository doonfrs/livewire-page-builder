<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class IconProperty extends BlockProperty
{
    public array $styles = [];

    public array $sets = [];

    public function __construct(
        string $name,
        ?string $label = null,
        array $styles = ['outline', 'solid', 'mini'],
        array|string|null $setsOrDefaultValue = null,
        $defaultValue = null,
    ) {
        // Backward compatibility: if 4th parameter is not an array, treat it as defaultValue (old signature)
        if (! is_array($setsOrDefaultValue)) {
            $defaultValue = $setsOrDefaultValue;
            $this->sets = ['heroicons'];
        } else {
            $this->sets = $setsOrDefaultValue;
        }

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
            'sets' => $this->sets,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Create a new instance of this property
     *
     * For backward compatibility, supports both old and new signatures:
     * Old: make($name, $label, $styles, $defaultValue)
     * New: make($name, $label, $styles, sets: [...], defaultValue: ...)
     */
    public static function make(
        string $name,
        ?string $label = null,
        array $styles = ['outline', 'solid', 'mini'],
        array|string|null $sets = null,
        $defaultValue = null
    ): static {
        // When $sets is null or not provided, use default ['heroicons']
        if ($sets === null) {
            $sets = ['heroicons'];
        }

        return new self(name: $name, label: $label, styles: $styles, setsOrDefaultValue: $sets, defaultValue: $defaultValue);
    }
}
