<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

use Illuminate\Support\Str;

class ResponsiveSpacingProperty extends BlockProperty
{
    protected array $defaultValues = [];

    protected array $directions = ['top', 'right', 'bottom', 'left'];

    protected array $devices = [
        'desktop' => ['label' => 'Desktop', 'icon' => 'heroicon-o-computer-desktop'],
        'tablet' => ['label' => 'Tablet', 'icon' => 'heroicon-o-device-tablet'],
        'mobile' => ['label' => 'Mobile', 'icon' => 'heroicon-o-device-phone-mobile'],
    ];

    public function __construct(
        string $name,
        ?string $label = null,
        array $defaultValues = []
    ) {
        parent::__construct($name, $label);

        $this->defaultValues = $defaultValues;
    }

    public function getType(): string
    {
        return 'responsive-spacing';
    }

    public function toArray(): array
    {
        $studlyName = Str::studly($this->name);
        $directionLabels = $this->getDirectionLabels();

        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'devices' => $this->devices,
            'directions' => $directionLabels,
            'fields' => $this->buildFieldMap($studlyName, $directionLabels),
            'values' => $this->normalizeDefaultValues($directionLabels),
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
        ];
    }

    /**
     * Get the default values mapped to their actual property names
     */
    public function getFieldDefaults(): array
    {
        $studlyName = Str::studly($this->name);
        $directionLabels = $this->getDirectionLabels();
        $fields = $this->buildFieldMap($studlyName, $directionLabels);
        $defaults = [];

        foreach ($fields as $device => $directions) {
            foreach ($directions as $directionKey => $fieldName) {
                $defaults[$fieldName] = $this->defaultValues[$device][$directionKey] ?? null;
            }
        }

        return $defaults;
    }

    protected function buildFieldMap(string $studlyName, array $directionLabels): array
    {
        $fields = [];

        foreach (array_keys($this->devices) as $device) {
            foreach (array_keys($directionLabels) as $directionKey) {
                $fields[$device][$directionKey] = $this->buildPropertyName($device, $studlyName, $directionKey);
            }
        }

        return $fields;
    }

    protected function buildPropertyName(string $device, string $studlyName, string $direction): string
    {
        return $device.$studlyName.Str::studly($direction);
    }

    protected function normalizeDefaultValues(array $directionLabels): array
    {
        $values = [];

        foreach (array_keys($this->devices) as $device) {
            foreach (array_keys($directionLabels) as $directionKey) {
                $values[$device][$directionKey] = $this->defaultValues[$device][$directionKey] ?? null;
            }
        }

        return $values;
    }

    protected function getDirectionLabels(): array
    {
        $labels = [];

        foreach ($this->directions as $direction) {
            $labels[$direction] = Str::headline($direction);
        }

        return $labels;
    }

    /**
     * Create a new instance of this property
     */
    public static function make(string $name, ?string $label = null, array $defaultValues = []): static
    {
        return new static($name, $label, $defaultValues);
    }
}
