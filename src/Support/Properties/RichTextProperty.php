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
     * Quill's toolbar alone is most of the live edit sheet's default height, and what is
     * left over is not an editing surface. This is the one built-in widget that asks for
     * the tall sheet; a plain textarea (SimpleTextProperty) fits in the short one.
     */
    public function needsRoom(): bool
    {
        return true;
    }

    /**
     * Enable or disable multilingual support for this property
     */
    public function multilingual(bool $value = true): static
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
    public static function make(string $name, ?string $label = null, $defaultValue = null, bool $multilingual = true): static
    {
        // @phpstan-ignore new.static
        return new static($name, $label, $defaultValue, $multilingual);
    }
}
