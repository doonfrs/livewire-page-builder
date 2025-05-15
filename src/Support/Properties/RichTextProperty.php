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

        // Use current app locale as default
        $currentLocale = app()->getLocale();

        // Initialize values for all locales
        $this->localizedValues = array_fill_keys(array_keys($contentLocales), '');

        // Set the default value for the current locale
        if (array_key_exists($currentLocale, $contentLocales)) {
            $this->localizedValues[$currentLocale] = $value;
        } else {
            // Fallback to the first locale if current locale isn't in content locales
            $firstLocale = array_key_first($contentLocales);
            if ($firstLocale) {
                $this->localizedValues[$firstLocale] = $value;
            }
        }
    }

    public function getType(): string
    {
        return 'richtext';
    }

    /**
     * Enable or disable multilingual support for this property
     */
    public function multilingual(bool $value = true): self
    {
        $this->multilingual = $value;
        return $this;
    }

    public function toArray(): array
    {
        $baseArray = [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'group' => $this->group,
            'groupLabel' => $this->groupLabel,
            'groupIcon' => $this->groupIcon,
            'groupColumns' => $this->groupColumns,
            'multilingual' => $this->multilingual,
        ];

        if ($this->multilingual) {
            $baseArray['defaultValue'] = app(LocalizationService::class)->createMultilingualContent($this->localizedValues);
            $baseArray['localizedValues'] = $this->localizedValues;
        } else {
            $baseArray['defaultValue'] = $this->defaultValue;
        }

        return $baseArray;
    }

    /**
     * Create a new instance of this property
     */
    public static function make(string $name, ?string $label = null, $defaultValue = null, bool $multilingual = true): self
    {
        return new self($name, $label, $defaultValue, $multilingual);
    }
}
