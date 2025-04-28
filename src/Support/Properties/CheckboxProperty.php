<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

class CheckboxProperty extends BlockProperty
{
    public function getType(): string
    {
        return 'checkbox';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
        ];
    }
}
