<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

/**
 * Flexible padding property with device-specific controls
 * Supports both class-based and custom value inputs with unified or individual controls
 */
class PaddingProperty extends BlockProperty
{
    protected array $paddingClasses;

    protected string $unit = 'px';

    public function __construct(
        string $name,
        string $label,
        array $paddingClasses = [],
        string $unit = 'px',
        mixed $defaultValue = null
    ) {
        parent::__construct($name, $label, $defaultValue);

        $this->paddingClasses = $paddingClasses ?: $this->getDefaultPaddingClasses();
        $this->unit = $unit;
    }

    protected function getDefaultPaddingClasses(): array
    {
        return [
            '' => 'None',
            'custom' => 'Custom',
            'p-0' => '0',
            'p-0.5' => '0.5',
            'p-1' => '1',
            'p-1.5' => '1.5',
            'p-2' => '2',
            'p-2.5' => '2.5',
            'p-3' => '3',
            'p-3.5' => '3.5',
            'p-4' => '4',
            'p-5' => '5',
            'p-6' => '6',
            'p-7' => '7',
            'p-8' => '8',
            'p-10' => '10',
            'p-12' => '12',
            'p-16' => '16',
            'p-20' => '20',
            'p-24' => '24',
            'p-32' => '32',
        ];
    }

    public function getType(): string
    {
        return 'padding';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'paddingClasses' => $this->paddingClasses,
            'unit' => $this->unit,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }
}
