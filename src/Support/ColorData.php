<?php

namespace Trinavo\LivewirePageBuilder\Support;

/**
 * Value object representing color data that can be either a CSS color (hex, rgb, rgba, etc.)
 * or a Tailwind CSS class.
 */
class ColorData
{
    public function __construct(
        public readonly string $type,      // 'css' | 'class' | 'none'
        public readonly ?string $value
    ) {}

    /**
     * Check if the color is a CSS color (hex, rgb, rgba, hsl, hsla)
     */
    public function isCss(): bool
    {
        return $this->type === 'css';
    }

    /**
     * Check if the color is a Tailwind class
     */
    public function isClass(): bool
    {
        return $this->type === 'class';
    }

    /**
     * Check if no color is set
     */
    public function isEmpty(): bool
    {
        return $this->type === 'none' || empty($this->value);
    }

    /**
     * Get the color as a CSS class string (empty if not a class)
     */
    public function toClass(): string
    {
        return $this->isClass() ? $this->value : '';
    }

    /**
     * Get the color as an inline CSS style property
     *
     * @param  string  $property  The CSS property name (e.g., 'background-color', 'color', 'border-color')
     */
    public function toInlineStyle(string $property = 'background-color'): string
    {
        return $this->isCss() ? "{$property}: {$this->value};" : '';
    }

    /**
     * Get the color as a complete style attribute string
     *
     * @param  string  $property  The CSS property name (e.g., 'background-color', 'color', 'border-color')
     */
    public function toStyleAttribute(string $property = 'background-color'): string
    {
        $style = $this->toInlineStyle($property);

        return $style ? "style=\"{$style}\"" : '';
    }

    /**
     * Get the color as a CSS variable value
     * Useful for setting CSS custom properties
     */
    public function toCssVariable(): string
    {
        return $this->value ?? '';
    }

    /**
     * Helper method to add a prefix to a Tailwind class, removing any existing prefix
     */
    protected function toClassWithPrefix(string $prefix): string
    {
        if (! $this->isClass() || $this->isEmpty()) {
            return '';
        }

        $value = $this->value;

        // Remove common prefixes if they exist
        $prefixes = ['bg-', 'text-', 'border-', 'decoration-', 'shadow-', 'ring-',
            'hover:bg-', 'hover:text-', 'hover:border-', 'hover:decoration-',
            'active:bg-', 'active:text-', 'active:border-', 'active:decoration-',
            'focus:bg-', 'focus:text-', 'focus:border-', 'focus:ring-'];

        foreach ($prefixes as $existingPrefix) {
            if (str_starts_with($value, $existingPrefix)) {
                $value = substr($value, strlen($existingPrefix));
                break;
            }
        }

        return $prefix.$value;
    }

    // ==================== Basic Class Helpers ====================

    /**
     * Get the color as a background class (bg-{color})
     */
    public function toBgClass(): string
    {
        return $this->toClassWithPrefix('bg-');
    }

    /**
     * Get the color as a text class (text-{color})
     */
    public function toTextClass(): string
    {
        return $this->toClassWithPrefix('text-');
    }

    /**
     * Get the color as a border class (border-{color})
     */
    public function toBorderClass(): string
    {
        return $this->toClassWithPrefix('border-');
    }

    /**
     * Get the color as a decoration/underline class (decoration-{color})
     */
    public function toDecorationClass(): string
    {
        return $this->toClassWithPrefix('decoration-');
    }

    /**
     * Get the color as a shadow class (shadow-{color})
     */
    public function toShadowClass(): string
    {
        return $this->toClassWithPrefix('shadow-');
    }

    /**
     * Get the color as a ring class (ring-{color})
     */
    public function toRingClass(): string
    {
        return $this->toClassWithPrefix('ring-');
    }

    // ==================== Hover State Class Helpers ====================

    /**
     * Get the color as a hover background class (hover:bg-{color})
     */
    public function toHoverBgClass(): string
    {
        return $this->toClassWithPrefix('hover:bg-');
    }

    /**
     * Get the color as a hover text class (hover:text-{color})
     */
    public function toHoverTextClass(): string
    {
        return $this->toClassWithPrefix('hover:text-');
    }

    /**
     * Get the color as a hover border class (hover:border-{color})
     */
    public function toHoverBorderClass(): string
    {
        return $this->toClassWithPrefix('hover:border-');
    }

    /**
     * Get the color as a hover decoration class (hover:decoration-{color})
     */
    public function toHoverDecorationClass(): string
    {
        return $this->toClassWithPrefix('hover:decoration-');
    }

    // ==================== Active State Class Helpers ====================

    /**
     * Get the color as an active background class (active:bg-{color})
     */
    public function toActiveBgClass(): string
    {
        return $this->toClassWithPrefix('active:bg-');
    }

    /**
     * Get the color as an active text class (active:text-{color})
     */
    public function toActiveTextClass(): string
    {
        return $this->toClassWithPrefix('active:text-');
    }

    /**
     * Get the color as an active border class (active:border-{color})
     */
    public function toActiveBorderClass(): string
    {
        return $this->toClassWithPrefix('active:border-');
    }

    /**
     * Get the color as an active decoration class (active:decoration-{color})
     */
    public function toActiveDecorationClass(): string
    {
        return $this->toClassWithPrefix('active:decoration-');
    }

    // ==================== Focus State Class Helpers ====================

    /**
     * Get the color as a focus background class (focus:bg-{color})
     */
    public function toFocusBgClass(): string
    {
        return $this->toClassWithPrefix('focus:bg-');
    }

    /**
     * Get the color as a focus text class (focus:text-{color})
     */
    public function toFocusTextClass(): string
    {
        return $this->toClassWithPrefix('focus:text-');
    }

    /**
     * Get the color as a focus border class (focus:border-{color})
     */
    public function toFocusBorderClass(): string
    {
        return $this->toClassWithPrefix('focus:border-');
    }

    /**
     * Get the color as a focus ring class (focus:ring-{color})
     */
    public function toFocusRingClass(): string
    {
        return $this->toClassWithPrefix('focus:ring-');
    }

    /**
     * Create a ColorData instance from a color value
     *
     * @param  string|null  $color  The color value (Tailwind class, hex, rgb, rgba, hsl, hsla)
     * @param  string|null  $defaultClass  Default Tailwind class if color is null/empty
     */
    public static function parse(?string $color, ?string $defaultClass = null): self
    {
        $color = $color ?: $defaultClass;

        if (empty($color)) {
            return new self('none', null);
        }

        if (self::isCssColor($color)) {
            return new self('css', $color);
        }

        return new self('class', $color);
    }

    /**
     * Check if a color value is a CSS color (hex, rgb, rgba, hsl, hsla)
     */
    protected static function isCssColor(string $color): bool
    {
        return str_starts_with($color, '#')
            || str_starts_with($color, 'rgb(')
            || str_starts_with($color, 'rgba(')
            || str_starts_with($color, 'hsl(')
            || str_starts_with($color, 'hsla(');
    }
}
