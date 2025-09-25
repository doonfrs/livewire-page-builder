<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class ResponsiveSpacingProperty extends Component
{
    public array $property = [];

    public array $values = [];

    public ?string $rowId = null;

    public ?string $blockId = null;

    public string $mode = 'all';

    public string $activeDevice = 'desktop';

    protected array $directionKeys = ['top', 'right', 'bottom', 'left'];

    public function mount($property, $values = [], $rowId = null, $blockId = null): void
    {
        $this->property = $property;
        $this->rowId = $rowId;
        $this->blockId = $blockId;

        $this->directionKeys = array_keys($property['directions'] ?? []);

        $this->initializeValues($values ?? []);
        $this->determineInitialMode();
    }

    public function updatedMode($value): void
    {
        if ($value === 'all') {
            $firstDevice = array_key_first($this->property['devices'] ?? []);
            if ($firstDevice && isset($this->values[$firstDevice])) {
                $this->values['all'] = $this->values[$firstDevice];
            }
        } else {
            if (! isset($this->property['devices'][$this->activeDevice])) {
                $this->activeDevice = array_key_first($this->property['devices'] ?? []) ?? 'desktop';
            }
        }
    }

    public function updated($name, $value): void
    {
        if (! str_starts_with($name, 'values.')) {
            return;
        }

        $path = substr($name, strlen('values.'));
        $segments = explode('.', $path, 2);

        if (count($segments) !== 2) {
            return;
        }

        [$target, $direction] = $segments;

        if (! in_array($direction, $this->directionKeys, true)) {
            return;
        }

        if ($target === 'all') {
            if ($this->mode !== 'all') {
                return;
            }

            $this->values['all'][$direction] = $value;
            $this->syncAllDevices($direction, $value);

            return;
        }

        if (! isset($this->property['devices'][$target])) {
            return;
        }

        $this->values[$target][$direction] = $value;
        $this->dispatchUpdate($target, $direction, $value);
    }

    protected function initializeValues(array $values): void
    {
        $blank = [];
        foreach ($this->directionKeys as $direction) {
            $blank[$direction] = null;
        }

        foreach (array_keys($this->property['devices'] ?? []) as $device) {
            $deviceValues = $values[$device] ?? [];
            $normalized = $blank;
            foreach ($this->directionKeys as $direction) {
                if (array_key_exists($direction, $deviceValues)) {
                    $normalized[$direction] = $deviceValues[$direction];
                }
            }
            $this->values[$device] = $normalized;
        }

        $this->values['all'] = $blank;
    }

    protected function determineInitialMode(): void
    {
        $devices = array_keys($this->property['devices'] ?? []);
        $firstDevice = $devices[0] ?? null;

        if (! $firstDevice) {
            return;
        }

        $this->values['all'] = $this->values[$firstDevice];

        if ($this->devicesShareSameValues($devices)) {
            $this->mode = 'all';
        } else {
            $this->mode = 'per-device';
            $this->activeDevice = $firstDevice;
        }
    }

    protected function devicesShareSameValues(array $devices): bool
    {
        if (count($devices) < 2) {
            return true;
        }

        $baseline = $this->values[$devices[0]] ?? [];

        foreach (array_slice($devices, 1) as $device) {
            if (($this->values[$device] ?? []) !== $baseline) {
                return false;
            }
        }

        return true;
    }

    protected function syncAllDevices(string $direction, $value): void
    {
        foreach (array_keys($this->property['devices'] ?? []) as $device) {
            $this->values[$device][$direction] = $value;
            $this->dispatchUpdate($device, $direction, $value);
        }
    }

    protected function dispatchUpdate(string $device, string $direction, $value): void
    {
        $propertyName = $this->property['fields'][$device][$direction] ?? null;

        if (! $propertyName) {
            return;
        }

        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $propertyName, $value);
    }

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.responsive-spacing-property');
    }
}
