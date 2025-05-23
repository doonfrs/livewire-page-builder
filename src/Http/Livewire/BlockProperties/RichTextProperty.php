<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Config\Variables;
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

class RichTextProperty extends Component
{
    public $rowId = null;

    public $blockId = null;

    public $propertyName;

    public $propertyLabel;

    public $currentValue;

    // Multilingual support properties
    public $multilingual = true;

    public $localizedValues = [];

    public $currentLocale = null;

    public $contentLocales = [];

    // Make variables public so it's accessible directly in the template
    public $variables = [];

    /**
     * Get the localization service instance
     */
    protected function getLocalizationService(): LocalizationService
    {
        return app(LocalizationService::class);
    }

    public function mount()
    {
        // Get the localization service
        $localizationService = $this->getLocalizationService();

        // Get locales directly from the service
        $this->contentLocales = $localizationService->getContentLocales();

        // Set current locale to the application locale
        $this->currentLocale = app()->getLocale();

        // Fallback to first content locale if app locale isn't in content locales
        if (! array_key_exists($this->currentLocale, $this->contentLocales)) {
            $this->currentLocale = array_key_first($this->contentLocales);
        }

        // Initialize localized values if they don't exist
        if (empty($this->localizedValues) && ! empty($this->currentValue)) {
            if (is_array($this->currentValue) && isset($this->currentValue['values'])) {
                // Already in multilingual format
                $this->localizedValues = $this->currentValue['values'];
                $this->multilingual = $this->currentValue['multilingual'] ?? true;
            } else {
                // Convert single value to multilingual format
                foreach (array_keys($this->contentLocales) as $locale) {
                    $this->localizedValues[$locale] = $locale === $this->currentLocale ? $this->currentValue : '';
                }
            }
        }

        // Initialize the current value for the editor
        if ($this->multilingual) {
            $this->currentValue = $this->localizedValues[$this->currentLocale] ?? '';
        }

        // Load the variables when the component is mounted
        $this->loadVariables();
    }

    /**
     * Load all available variables to display in the variables modal
     */
    protected function loadVariables()
    {
        $variablesArray = Variables::all();
        $formattedVariables = [];

        foreach ($variablesArray as $name => $value) {
            // Format the variable for display
            $formattedVariables[] = [
                'name' => $name,
                'value' => is_string($value) || is_numeric($value) ? $value : '[Dynamic Value]',
            ];
        }

        $this->variables = $formattedVariables;
    }

    public function switchLocale($locale)
    {
        // Store the original locale before switching
        $oldLocale = $this->currentLocale;

        // First, save current content to the CURRENT locale
        if ($this->multilingual && $oldLocale) {
            $this->localizedValues[$oldLocale] = $this->currentValue;
        }

        // Then switch to new locale
        $this->currentLocale = $locale;

        // Load content for the new locale
        $this->currentValue = $this->localizedValues[$locale] ?? '';

        // Emit an event to inform the Alpine component
        $this->dispatch('localeChanged', $this->currentLocale);
    }

    public function refreshContent()
    {
        // This method can be called from the frontend to refresh the content
        return $this->currentValue;
    }

    public function updatedCurrentValue()
    {
        if ($this->multilingual) {
            // Update the current locale's value
            $this->localizedValues[$this->currentLocale] = $this->currentValue;

            // Create a structured value for storage using the service
            $valueToStore = $this->getLocalizationService()->createMultilingualContent(
                $this->localizedValues,
                app()->getLocale() // Use current app locale as default
            );

            $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $valueToStore);
        } else {
            // Single language mode - just update the current value
            $this->updateProperty();
        }
    }

    protected function updateProperty()
    {
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $this->currentValue);
    }

    public function render()
    {
        // Always refresh content locales before rendering in case they've changed
        $this->contentLocales = $this->getLocalizationService()->getContentLocales();

        // Refresh variables in case any have been added or changed
        $this->loadVariables();

        return view('page-builder::livewire.builder.block-properties.rich-text');
    }
}
