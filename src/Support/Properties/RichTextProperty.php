<?php

namespace Trinavo\LivewirePageBuilder\Support\Properties;

use Trinavo\LivewirePageBuilder\Services\LocalizationService;

class RichTextProperty extends BlockProperty
{
    /**
     * Flag to indicate if this property should support multiple languages
     */
    public bool $multilingual = true;

    /**
     * Stores localized values for each language
     */
    public array $localizedValues = [];

    public function __construct(
        string $name,
        ?string $label = null,
        $defaultValue = null,
        bool $multilingual = true
    ) {
        parent::__construct($name, $label, $defaultValue);
        $this->multilingual = $multilingual;

        // Initialize with default value if provided
        if ($defaultValue !== null) {
            $this->setDefaultForAllLocales($defaultValue);
        }
    }

    /**
     * Set a default value for all supported locales
     */
    protected function setDefaultForAllLocales($value): void
    {
        // Get localization service
        $localizationService = app(LocalizationService::class);
        $contentLocales = $localizationService->getContentLocales();
        $defaultLocale = $localizationService->getDefaultContentLocale();

        // Initialize values for all locales
        $this->localizedValues = array_fill_keys(array_keys($contentLocales), '');

        // Set the default value for the default locale
        $this->localizedValues[$defaultLocale] = $value;

    }

    public function getType(): string
    {
        return 'richtext';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'defaultValue' => $this->multilingual ?
                app(LocalizationService::class)->createMultilingualContent($this->localizedValues) :
                $this->defaultValue,
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
            'multilingual' => $this->multilingual,
            'localizedValues' => $this->localizedValues,
        ];
    }

    /**
     * Set this property to be multilingual or not
     */
    public function setMultilingual(bool $multilingual): self
    {
        $this->multilingual = $multilingual;
        return $this;
    }
}
