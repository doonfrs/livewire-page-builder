<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

/**
 * Flexible margin property with device-specific controls
 * Supports both class-based and custom value inputs with unified or individual controls
 */
class MarginProperty extends BlockProperty
{
    protected array $marginClasses;

    protected string $unit = 'px';

    public function __construct(
        string $name,
        string $label,
        array $marginClasses = [],
        string $unit = 'px',
        mixed $defaultValue = null
    ) {
        parent::__construct($name, $label, $defaultValue);

        $this->marginClasses = $marginClasses ?: $this->getDefaultMarginClasses();
        $this->unit = $unit;
    }

    protected function getDefaultMarginClasses(): array
    {
        return [
            '' => 'None',
            'custom' => 'Custom',
            'm-0' => '0',
            'm-0.5' => '0.5',
            'm-1' => '1',
            'm-1.5' => '1.5',
            'm-2' => '2',
            'm-2.5' => '2.5',
            'm-3' => '3',
            'm-3.5' => '3.5',
            'm-4' => '4',
            'm-5' => '5',
            'm-6' => '6',
            'm-7' => '7',
            'm-8' => '8',
            'm-10' => '10',
            'm-12' => '12',
            'm-16' => '16',
            'm-20' => '20',
            'm-24' => '24',
            'm-32' => '32',
        ];
    }

    public function getType(): string
    {
        return 'margin';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->defaultValue,
            'marginClasses' => $this->marginClasses,
            'unit' => $this->unit,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }
}
