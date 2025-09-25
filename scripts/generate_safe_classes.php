<?php

/**
 * Generate Tailwind CSS safe class lists for dynamic classes
 * Usage: php generate_safe_classes.php [options]
 *
 * Options:
 *   --type=TYPE       Type of classes to generate (all, height, min-height, width, min-width, padding, margin, grid, gap, flex, visibility, position, overflow, colors, shadows, alignment, complete) [default: all]
 *   --min=MIN         Minimum pixel value [default: 1]
 *   --max=MAX         Maximum pixel value [default: 500]
 *   --breakpoints=BP  Comma-separated list of breakpoints [default: xl,3xl,5xl]
 *   --output=DIR      Output directory [default: resources/views/dev]
 *   --help            Show this help message
 */
class SafeClassGenerator
{
    protected array $standardHeightClasses = [
        'auto', 'px', '0', '0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '5', '6', '7', '8', '9',
        '10', '11', '12', '14', '16', '20', '24', '28', '32', '36', '40', '44', '48', '52', '56',
        '60', '64', '72', '80', '96',
    ];

    protected array $specialHeightClasses = [
        'fit', 'min', 'max', 'full', 'screen', 'svh', 'lvh', 'dvh',
    ];

    protected array $fractionHeightClasses = [
        '1/2', '1/3', '2/3', '1/4', '2/4', '3/4', '1/5', '2/5', '3/5', '4/5',
        '1/6', '2/6', '3/6', '4/6', '5/6',
    ];

    protected array $standardWidthClasses = [
        'auto', 'px', '0', '0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '5', '6', '7', '8', '9',
        '10', '11', '12', '14', '16', '20', '24', '28', '32', '36', '40', '44', '48', '52', '56',
        '60', '64', '72', '80', '96',
    ];

    protected array $specialWidthClasses = [
        'fit', 'min', 'max', 'full', 'screen', 'svw', 'lvw', 'dvw',
        '3xs', '2xs', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl',
    ];

    protected array $fractionWidthClasses = [
        '1/2', '1/3', '2/3', '1/4', '2/4', '3/4', '1/5', '2/5', '3/5', '4/5',
        '1/6', '2/6', '3/6', '4/6', '5/6', '1/12', '2/12', '3/12', '4/12', '5/12',
        '6/12', '7/12', '8/12', '9/12', '10/12', '11/12',
    ];

    protected array $spacingClasses = [
        '0', 'px', '0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '5', '6', '7', '8', '9',
        '10', '11', '12', '14', '16', '20', '24', '28', '32', '36', '40', '44', '48', '52', '56',
        '60', '64', '72', '80', '96',
    ];

    protected array $gridColumns = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
    ];

    protected array $gapSizes = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16',
        '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31',
        '32', '33', '34', '35', '36', '37', '38', '39', '40',
    ];

    protected array $standardColors = [
        'gray' => ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
        'red' => ['300', '400', '500', '600', '700'],
        'blue' => ['300', '400', '500', '600', '700'],
        'green' => ['300', '400', '500', '600', '700'],
        'yellow' => ['300', '400', '500', '600', '700'],
        'pink' => ['300', '400', '500', '600', '700'],
        'purple' => ['300', '400', '500', '600', '700'],
        'indigo' => ['300', '400', '500', '600', '700'],
    ];

    protected array $daisyColors = [
        'base-100', 'base-200', 'base-300', 'base-content',
        'primary', 'primary-content',
        'secondary', 'secondary-content',
        'accent', 'accent-content',
        'neutral', 'neutral-content',
        'info', 'info-content',
        'success', 'success-content',
        'warning', 'warning-content',
        'error', 'error-content',
    ];

    protected array $shadowTypes = [
        'sm', '', 'md', 'lg', 'xl', '2xl', 'inner', 'none',
    ];

    public function run(array $options = []): void
    {
        $type = $options['type'] ?? 'all';
        $min = (int) ($options['min'] ?? 1);
        $max = (int) ($options['max'] ?? 500);
        $breakpoints = explode(',', $options['breakpoints'] ?? '3xl,5xl');
        $outputDir = $options['output'] ?? __DIR__.'/../resources/views/dev';

        // Ensure output directory exists
        if (! is_dir($outputDir)) {
            if (! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
                throw new RuntimeException("Failed to create directory: {$outputDir}");
            }
        }

        echo "Generating safe classes...\n";

        switch ($type) {
            case 'height':
                $this->generateHeightClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'min-height':
                $this->generateMinHeightClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'width':
                $this->generateWidthClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'min-width':
                $this->generateMinWidthClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'padding':
                $this->generatePaddingClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'margin':
                $this->generateMarginClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'grid':
                $this->generateGridClasses($outputDir, $breakpoints);
                break;
            case 'gap':
                $this->generateGapClasses($outputDir, $breakpoints);
                break;
            case 'flex':
                $this->generateFlexClasses($outputDir, $breakpoints);
                break;
            case 'visibility':
                $this->generateVisibilityClasses($outputDir, $breakpoints);
                break;
            case 'position':
                $this->generatePositionClasses($outputDir, $breakpoints);
                break;
            case 'overflow':
                $this->generateOverflowClasses($outputDir, $breakpoints);
                break;
            case 'colors':
                $this->generateColorClasses($outputDir, $breakpoints);
                break;
            case 'shadows':
                $this->generateShadowClasses($outputDir, $breakpoints);
                break;
            case 'alignment':
                $this->generateAlignmentClasses($outputDir, $breakpoints);
                break;
            case 'complete':
                $this->generateCompleteClasses($outputDir, $min, $max, $breakpoints);
                break;
            case 'all':
                $this->generateHeightClasses($outputDir, $min, $max, $breakpoints);
                $this->generateMinHeightClasses($outputDir, $min, $max, $breakpoints);
                $this->generateWidthClasses($outputDir, $min, $max, $breakpoints);
                $this->generateMinWidthClasses($outputDir, $min, $max, $breakpoints);
                $this->generatePaddingClasses($outputDir, $min, $max, $breakpoints);
                $this->generateMarginClasses($outputDir, $min, $max, $breakpoints);
                $this->generateGridClasses($outputDir, $breakpoints);
                $this->generateGapClasses($outputDir, $breakpoints);
                $this->generateFlexClasses($outputDir, $breakpoints);
                $this->generateVisibilityClasses($outputDir, $breakpoints);
                $this->generatePositionClasses($outputDir, $breakpoints);
                $this->generateOverflowClasses($outputDir, $breakpoints);
                $this->generateColorClasses($outputDir, $breakpoints);
                $this->generateShadowClasses($outputDir, $breakpoints);
                $this->generateAlignmentClasses($outputDir, $breakpoints);
                break;
            default:
                echo "Error: Unknown type: {$type}\n";
                exit(1);
        }

        echo "Safe classes generated successfully!\n";
    }

    protected function generateHeightClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $content = $this->generateClassContent('h', $this->standardHeightClasses, $this->specialHeightClasses, $this->fractionHeightClasses, $min, $max, $breakpoints);

        $filePath = $outputDir.'/safe-classes-height.blade.php';
        file_put_contents($filePath, $content);
        echo "Generated: {$filePath}\n";
    }

    protected function generateMinHeightClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $content = $this->generateClassContent('min-h', $this->standardHeightClasses, $this->specialHeightClasses, [], $min, $max, $breakpoints);

        $filePath = $outputDir.'/safe-classes-min-height.blade.php';
        file_put_contents($filePath, $content);
        echo "Generated: {$filePath}\n";
    }

    protected function generateWidthClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $content = $this->generateClassContent('w', $this->standardWidthClasses, $this->specialWidthClasses, $this->fractionWidthClasses, $min, $max, $breakpoints);

        $filePath = $outputDir.'/safe-classes-width.blade.php';
        file_put_contents($filePath, $content);
        echo "Generated: {$filePath}\n";
    }

    protected function generateMinWidthClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $content = $this->generateClassContent('min-w', $this->standardWidthClasses, $this->specialWidthClasses, [], $min, $max, $breakpoints);

        $filePath = $outputDir.'/safe-classes-min-width.blade.php';
        file_put_contents($filePath, $content);
        echo "Generated: {$filePath}\n";
    }

    protected function generatePaddingClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for padding --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Padding directions
        $directions = [
            'p' => 'all',
            'pt' => 'top',
            'pr' => 'right',
            'pb' => 'bottom',
            'pl' => 'left',
            'px' => 'horizontal',
            'py' => 'vertical',
        ];

        foreach ($directions as $prefix => $direction) {
            $lines[] = "{{-- Padding {$direction} classes --}}";
            $lines[] = $this->generateDivWithClasses($prefix, $this->spacingClasses);
            $lines[] = '';

            // Responsive classes
            foreach ($breakpoints as $breakpoint) {
                $breakpoint = trim($breakpoint);
                $lines[] = "{{-- @{$breakpoint} responsive padding {$direction} classes --}}";
                $lines[] = $this->generateDivWithClasses($prefix, $this->spacingClasses, "@{$breakpoint}");
                $lines[] = '';
            }
        }

        // Add arbitrary value classes for custom spacing
        $lines[] = $this->generateArbitraryPaddingClasses($min, $max, $breakpoints);

        $filePath = $outputDir.'/safe-classes-padding.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateMarginClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for margin --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Margin directions
        $directions = [
            'm' => 'all',
            'mt' => 'top',
            'mr' => 'right',
            'mb' => 'bottom',
            'ml' => 'left',
            'mx' => 'horizontal',
            'my' => 'vertical',
        ];

        foreach ($directions as $prefix => $direction) {
            // Positive margin classes
            $lines[] = "{{-- Margin {$direction} classes --}}";
            $lines[] = $this->generateDivWithClasses($prefix, $this->spacingClasses);
            $lines[] = '';

            // Negative margin classes
            $lines[] = "{{-- Negative margin {$direction} classes --}}";
            $negativeClasses = [];
            foreach ($this->spacingClasses as $spacing) {
                if ($spacing !== '0') { // Skip -0 as it's the same as 0
                    $negativeClasses[] = $spacing;
                }
            }
            $lines[] = $this->generateDivWithClasses('-'.$prefix, $negativeClasses);
            $lines[] = '';

            // Responsive classes - positive
            foreach ($breakpoints as $breakpoint) {
                $breakpoint = trim($breakpoint);
                $lines[] = "{{-- @{$breakpoint} responsive margin {$direction} classes --}}";
                $lines[] = $this->generateDivWithClasses($prefix, $this->spacingClasses, "@{$breakpoint}");
                $lines[] = '';

                // Responsive classes - negative
                $lines[] = "{{-- @{$breakpoint} responsive negative margin {$direction} classes --}}";
                $lines[] = $this->generateDivWithClasses('-'.$prefix, $negativeClasses, "@{$breakpoint}");
                $lines[] = '';
            }
        }

        // Add arbitrary value classes for custom spacing
        $lines[] = $this->generateArbitraryMarginClasses($min, $max, $breakpoints);

        $filePath = $outputDir.'/safe-classes-margin.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateGridClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for grid --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Grid column classes
        $lines[] = '{{-- Grid columns classes --}}';
        $gridColsClasses = [];
        foreach ($this->gridColumns as $col) {
            $gridColsClasses[] = "grid-cols-{$col}";
        }
        $lines[] = '<div class="grid '.implode(' ', $gridColsClasses).'"></div>';
        $lines[] = '';

        // Column span classes
        $lines[] = '{{-- Column span classes --}}';
        $colSpanClasses = [];
        foreach ($this->gridColumns as $col) {
            $colSpanClasses[] = "col-span-{$col}";
        }
        $lines[] = '<div class="'.implode(' ', $colSpanClasses).'"></div>';
        $lines[] = '';

        // Responsive grid and column span classes
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive grid classes --}}";

            $responsiveColSpanClasses = [];
            foreach ($this->gridColumns as $col) {
                $responsiveColSpanClasses[] = "@{$breakpoint}:col-span-{$col}";
            }
            $lines[] = '<div class="'.implode(' ', $responsiveColSpanClasses).'"></div>';
            $lines[] = '';
        }

        $filePath = $outputDir.'/safe-classes-grid.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateGapClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for gap --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Gap classes
        $lines[] = '{{-- Gap classes --}}';
        $gapClasses = [];
        foreach ($this->gapSizes as $gap) {
            $gapClasses[] = "gap-{$gap}";
        }
        $lines[] = '<div class="'.implode(' ', $gapClasses).'"></div>';
        $lines[] = '';

        // Responsive gap classes
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive gap classes --}}";

            $responsiveGapClasses = [];
            foreach ($this->gapSizes as $gap) {
                $responsiveGapClasses[] = "@{$breakpoint}:gap-{$gap}";
            }
            $lines[] = '<div class="'.implode(' ', $responsiveGapClasses).'"></div>';
            $lines[] = '';
        }

        $filePath = $outputDir.'/safe-classes-gap.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateFlexClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for flex --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        $flexClasses = ['flex', 'flex-row', 'flex-col'];

        $lines[] = '{{-- Flex classes --}}';
        $lines[] = '<div class="'.implode(' ', $flexClasses).'"></div>';
        $lines[] = '';

        // Responsive flex classes
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive flex classes --}}";

            $responsiveFlexClasses = [];
            foreach ($flexClasses as $flexClass) {
                $responsiveFlexClasses[] = "@{$breakpoint}:{$flexClass}";
            }
            $lines[] = '<div class="'.implode(' ', $responsiveFlexClasses).'"></div>';
            $lines[] = '';
        }

        $filePath = $outputDir.'/safe-classes-flex.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateVisibilityClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for visibility --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        $visibilityClasses = ['block', 'hidden', 'inline-block'];

        $lines[] = '{{-- Visibility classes --}}';
        $lines[] = '<div class="'.implode(' ', $visibilityClasses).'"></div>';
        $lines[] = '';

        // All breakpoint variants (both @ and standard)
        $allBreakpoints = array_merge(['sm', 'md', 'lg', 'xl', '2xl'], $breakpoints);

        foreach ($allBreakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);

            // Container query variants
            $lines[] = "{{-- @{$breakpoint} responsive visibility classes --}}";
            $responsiveVisibilityClasses = [];
            foreach ($visibilityClasses as $visClass) {
                $responsiveVisibilityClasses[] = "@{$breakpoint}:{$visClass}";
            }
            $lines[] = '<div class="'.implode(' ', $responsiveVisibilityClasses).'"></div>';
            $lines[] = '';

            // Standard responsive variants
            $lines[] = "{{-- {$breakpoint} responsive visibility classes --}}";
            $standardResponsiveClasses = [];
            foreach ($visibilityClasses as $visClass) {
                $standardResponsiveClasses[] = "{$breakpoint}:{$visClass}";
            }
            $lines[] = '<div class="'.implode(' ', $standardResponsiveClasses).'"></div>';
            $lines[] = '';
        }

        $filePath = $outputDir.'/safe-classes-visibility.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generatePositionClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for position --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        $positionClasses = ['static', 'relative', 'absolute', 'fixed', 'sticky'];

        $lines[] = '{{-- Position classes --}}';
        $lines[] = '<div class="'.implode(' ', $positionClasses).'"></div>';
        $lines[] = '';

        // All breakpoint variants
        $allBreakpoints = array_merge(['md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl'], $breakpoints);

        foreach ($allBreakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);

            // Container query variants
            $lines[] = "{{-- @{$breakpoint} responsive position classes --}}";
            $responsivePositionClasses = [];
            foreach ($positionClasses as $posClass) {
                $responsivePositionClasses[] = "@{$breakpoint}:{$posClass}";
            }
            $lines[] = '<div class="'.implode(' ', $responsivePositionClasses).'"></div>';
            $lines[] = '';

            // Standard responsive variants
            if (in_array($breakpoint, ['md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl'])) {
                $lines[] = "{{-- {$breakpoint} responsive position classes --}}";
                $standardResponsiveClasses = [];
                foreach ($positionClasses as $posClass) {
                    $standardResponsiveClasses[] = "{$breakpoint}:{$posClass}";
                }
                $lines[] = '<div class="'.implode(' ', $standardResponsiveClasses).'"></div>';
                $lines[] = '';
            }
        }

        $filePath = $outputDir.'/safe-classes-position.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateOverflowClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for overflow --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        $overflowTypes = ['auto', 'scroll', 'visible', 'hidden'];

        // Basic overflow classes
        $lines[] = '{{-- Overflow classes --}}';
        $overflowClasses = [];
        foreach ($overflowTypes as $type) {
            $overflowClasses[] = "overflow-{$type}";
        }
        $lines[] = '<div class="'.implode(' ', $overflowClasses).'"></div>';
        $lines[] = '';

        // Overflow-x classes
        $lines[] = '{{-- Overflow-x classes --}}';
        $overflowXClasses = [];
        foreach ($overflowTypes as $type) {
            $overflowXClasses[] = "overflow-x-{$type}";
        }
        $lines[] = '<div class="'.implode(' ', $overflowXClasses).'"></div>';
        $lines[] = '';

        // Overflow-y classes
        $lines[] = '{{-- Overflow-y classes --}}';
        $overflowYClasses = [];
        foreach ($overflowTypes as $type) {
            $overflowYClasses[] = "overflow-y-{$type}";
        }
        $lines[] = '<div class="'.implode(' ', $overflowYClasses).'"></div>';
        $lines[] = '';

        $filePath = $outputDir.'/safe-classes-overflow.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateColorClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for colors --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Text color classes - standard colors
        $lines[] = '{{-- Text color utilities --}}';
        $textColorClasses = [];
        foreach ($this->standardColors as $color => $shades) {
            foreach ($shades as $shade) {
                $textColorClasses[] = "text-{$color}-{$shade}";
            }
        }
        $lines[] = '<div class="'.implode(' ', $textColorClasses).'"></div>';
        $lines[] = '';

        // Text color classes - DaisyUI colors
        $lines[] = '{{-- DaisyUI Theme Text Color Utilities --}}';
        $daisyTextColors = [];
        foreach ($this->daisyColors as $color) {
            $daisyTextColors[] = "text-{$color}";
        }
        $lines[] = '<div class="'.implode(' ', $daisyTextColors).'"></div>';
        $lines[] = '';

        // Background color classes - standard colors
        $lines[] = '{{-- Background color utilities --}}';
        $bgColorClasses = [];
        foreach ($this->standardColors as $color => $shades) {
            foreach ($shades as $shade) {
                $bgColorClasses[] = "bg-{$color}-{$shade}";
            }
        }
        $lines[] = '<div class="'.implode(' ', $bgColorClasses).'"></div>';
        $lines[] = '';

        // Background color classes - DaisyUI colors
        $lines[] = '{{-- DaisyUI Theme Background Color Utilities --}}';
        $daisyBgColors = [];
        foreach ($this->daisyColors as $color) {
            $daisyBgColors[] = "bg-{$color}";
        }
        $lines[] = '<div class="'.implode(' ', $daisyBgColors).'"></div>';
        $lines[] = '';

        $filePath = $outputDir.'/safe-classes-colors.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateShadowClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for shadows --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '<div style="display: none;">';
        $lines[] = '';

        // Basic shadow classes
        $lines[] = '    <!-- Box Shadow Classes -->';
        $shadowClasses = [];
        foreach ($this->shadowTypes as $type) {
            $shadowClasses[] = $type === '' ? 'shadow' : "shadow-{$type}";
        }
        $shadowClasses[] = 'shadow-custom';
        $lines[] = '    <div class="'.implode(' ', $shadowClasses).'"></div>';
        $lines[] = '';

        // Shadow color classes - standard colors
        $lines[] = '    <!-- Box Shadow Color Classes -->';
        foreach ($this->standardColors as $color => $shades) {
            $shadowColorClasses = [];
            foreach ($shades as $shade) {
                $shadowColorClasses[] = "shadow-{$color}-{$shade}";
            }
            $lines[] = '    <div class="'.implode(' ', $shadowColorClasses).'"></div>';
        }
        $lines[] = '';

        // Shadow color classes - DaisyUI colors
        $lines[] = '    <!-- Box Shadow Theme Color Classes -->';
        $daisyShadowColors = [];
        foreach ($this->daisyColors as $color) {
            $daisyShadowColors[] = "shadow-{$color}";
        }
        // Split into logical groups for readability
        $baseColors = array_slice($daisyShadowColors, 0, 4);
        $themeColors = array_slice($daisyShadowColors, 4);

        $lines[] = '    <div class="'.implode(' ', $baseColors).'"></div>';
        for ($i = 0; $i < count($themeColors); $i += 2) {
            $group = array_slice($themeColors, $i, 2);
            $lines[] = '    <div class="'.implode(' ', $group).'"></div>';
        }

        $lines[] = '</div>';

        $filePath = $outputDir.'/safe-classes-shadows.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateAlignmentClasses(string $outputDir, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated Tailwind safe classes for alignment --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Alignment classes
        $itemsClasses = ['items-start', 'items-center', 'items-end', 'items-baseline', 'items-stretch'];
        $justifyClasses = ['justify-start', 'justify-center', 'justify-end', 'justify-between', 'justify-around', 'justify-evenly', 'justify-stretch'];
        $contentClasses = ['content-start', 'content-center', 'content-end', 'content-baseline', 'content-stretch'];

        $lines[] = '{{-- Items alignment classes --}}';
        $lines[] = '<div class="'.implode(' ', $itemsClasses).'"></div>';
        $lines[] = '';

        $lines[] = '{{-- Justify content classes --}}';
        $lines[] = '<div class="'.implode(' ', $justifyClasses).'"></div>';
        $lines[] = '';

        $lines[] = '{{-- Content alignment classes --}}';
        $lines[] = '<div class="'.implode(' ', $contentClasses).'"></div>';
        $lines[] = '';

        // Container class
        $lines[] = '{{-- Layout classes --}}';
        $lines[] = '<div class="container mx-auto md:container lg:container"></div>';
        $lines[] = '';

        $filePath = $outputDir.'/safe-classes-alignment.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    protected function generateCompleteClasses(string $outputDir, int $min, int $max, array $breakpoints): void
    {
        $lines = [];
        $lines[] = '{{-- Auto-generated complete Tailwind safe classes --}}';
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '<div style="display: none;">';
        $lines[] = '';

        // Include all the individual components
        $this->addGridToComplete($lines, $breakpoints);
        $this->addWidthToComplete($lines, $breakpoints);
        $this->addHeightToComplete($lines, $breakpoints);
        $this->addMinHeightToComplete($lines, $breakpoints);
        $this->addFlexToComplete($lines, $breakpoints);
        $this->addGapToComplete($lines, $breakpoints);
        $this->addVisibilityToComplete($lines, $breakpoints);
        $this->addPositionToComplete($lines, $breakpoints);
        $this->addColorsToComplete($lines);
        $this->addOverflowToComplete($lines);
        $this->addAlignmentToComplete($lines);
        $this->addPaddingToComplete($lines, $breakpoints);
        $this->addMarginToComplete($lines, $breakpoints);

        $lines[] = '</div>';

        $filePath = $outputDir.'/safe-classes.blade.php';
        file_put_contents($filePath, implode("\n", $lines));
        echo "Generated: {$filePath}\n";
    }

    // Helper methods for complete generation
    protected function addGridToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '    {{-- Grid classes --}}';

        // Grid column classes
        $gridColsClasses = [];
        foreach ($this->gridColumns as $col) {
            $gridColsClasses[] = "grid-cols-{$col}";
        }
        $lines[] = '    <div class="grid '.implode(' ', $gridColsClasses).'"></div>';
        $lines[] = '';

        // Column span classes
        $colSpanClasses = [];
        foreach ($this->gridColumns as $col) {
            $colSpanClasses[] = "col-span-{$col}";
        }
        $lines[] = '    <div class="'.implode(' ', $colSpanClasses).'"></div>';
        $lines[] = '';

        // Responsive variants
        $allBreakpoints = array_merge(['md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl'], $breakpoints);
        foreach ($allBreakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $responsiveColSpanClasses = [];
            foreach ($this->gridColumns as $col) {
                $responsiveColSpanClasses[] = "@{$breakpoint}:col-span-{$col}";
            }
            $lines[] = '    <div class="'.implode(' ', $responsiveColSpanClasses).'"></div>';
        }
        $lines[] = '';
    }

    protected function addWidthToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '    {{-- Width utilities --}}';
        $widthClasses = ['w-1/10', 'w-1/5', 'w-3/10', 'w-2/5', 'w-1/2', 'w-3/5', 'w-7/10', 'w-4/5', 'w-9/10', 'w-1/3', 'w-1/4', 'w-2/3', 'w-3/4', 'w-full', 'w-auto', 'w-screen', 'w-fit', 'w-3xs', 'w-2xs', 'w-xs', 'w-sm', 'w-md', 'w-lg', 'w-xl', 'w-2xl', 'w-3xl', 'w-4xl', 'w-5xl', 'w-6xl', 'w-7xl'];
        $lines[] = '    <div class="'.implode(' ', $widthClasses).'"></div>';

        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $responsiveWidthClasses = [];
            foreach ($widthClasses as $widthClass) {
                $responsiveWidthClasses[] = "@{$breakpoint}:{$widthClass}";
            }
            $lines[] = '    <div class="'.implode(' ', $responsiveWidthClasses).'"></div>';
        }
        $lines[] = '';
    }

    protected function addHeightToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '    {{-- Height utilities --}}';
        $heightClasses = ['h-0', 'h-px', 'h-1', 'h-2', 'h-4', 'h-8', 'h-12', 'h-16', 'h-24', 'h-32', 'h-48', 'h-64', 'h-80', 'h-96', 'h-1/2', 'h-1/3', 'h-2/3', 'h-1/4', 'h-3/4', 'h-1/5', 'h-2/5', 'h-3/5', 'h-4/5', 'h-full', 'h-screen', 'h-auto'];
        $lines[] = '    <div class="'.implode(' ', $heightClasses).'"></div>';

        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $responsiveHeightClasses = [];
            foreach ($heightClasses as $heightClass) {
                $responsiveHeightClasses[] = "@{$breakpoint}:{$heightClass}";
            }
            $lines[] = '    <div class="'.implode(' ', $responsiveHeightClasses).'"></div>';
        }
        $lines[] = '';
    }

    protected function addMinHeightToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '    {{-- Min Height utilities --}}';
        $minHeightClasses = ['min-h-0', 'min-h-px', 'min-h-1', 'min-h-2', 'min-h-4', 'min-h-8', 'min-h-12', 'min-h-16', 'min-h-24', 'min-h-32', 'min-h-48', 'min-h-64', 'min-h-80', 'min-h-96', 'min-h-1/2', 'min-h-1/3', 'min-h-2/3', 'min-h-1/4', 'min-h-3/4', 'min-h-1/5', 'min-h-2/5', 'min-h-3/5', 'min-h-4/5', 'min-h-full', 'min-h-screen'];
        $lines[] = '    <div class="'.implode(' ', $minHeightClasses).'"></div>';

        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $responsiveMinHeightClasses = [];
            foreach ($minHeightClasses as $minHeightClass) {
                $responsiveMinHeightClasses[] = "@{$breakpoint}:{$minHeightClass}";
            }
            $lines[] = '    <div class="'.implode(' ', $responsiveMinHeightClasses).'"></div>';
        }
        $lines[] = '';
    }

    protected function addFlexToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '    <div class="flex flex-row flex-col"></div>';

        $standardBreakpoints = ['md', 'lg'];
        foreach ($standardBreakpoints as $breakpoint) {
            $lines[] = "    <div class=\"@{$breakpoint}:flex @{$breakpoint}:flex-row @{$breakpoint}:flex-col\"></div>";
        }
        $lines[] = '';
    }

    protected function addGapToComplete(array &$lines, array $breakpoints): void
    {
        $gapClasses = [];
        foreach ($this->gapSizes as $gap) {
            $gapClasses[] = "gap-{$gap}";
        }
        $lines[] = '    <div class="'.implode(' ', $gapClasses).'"></div>';

        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $responsiveGapClasses = [];
            foreach ($this->gapSizes as $gap) {
                $responsiveGapClasses[] = "@{$breakpoint}:gap-{$gap}";
            }
            $lines[] = '    <div class="'.implode(' ', $responsiveGapClasses).'"></div>';
        }
        $lines[] = '';
    }

    protected function addVisibilityToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '';
        $lines[] = '    {{-- Responsive visibility --}}';

        $allBreakpoints = ['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl'];
        $containerQueryClasses = [];
        $standardClasses = [];

        foreach ($allBreakpoints as $breakpoint) {
            $containerQueryClasses[] = "@{$breakpoint}:block";
            $containerQueryClasses[] = "@{$breakpoint}:hidden";
            $standardClasses[] = "{$breakpoint}:block";
            $standardClasses[] = "{$breakpoint}:hidden";
        }

        $lines[] = '    <div class="'.implode(' ', $containerQueryClasses).'"></div>';
        $lines[] = '    <div class="'.implode(' ', $standardClasses).'"></div>';
        $lines[] = '    <div class="inline-block block hidden"></div>';
        $lines[] = '';
    }

    protected function addPositionToComplete(array &$lines, array $breakpoints): void
    {
        $lines[] = '    {{-- Position utilities --}}';
        $positionClasses = ['static', 'relative', 'absolute', 'fixed', 'sticky'];
        $lines[] = '    <div class="'.implode(' ', $positionClasses).'"></div>';

        $allBreakpoints = ['md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl'];
        foreach ($allBreakpoints as $breakpoint) {
            $containerQueryClasses = [];
            $standardClasses = [];
            foreach ($positionClasses as $posClass) {
                $containerQueryClasses[] = "@{$breakpoint}:{$posClass}";
                $standardClasses[] = "{$breakpoint}:{$posClass}";
            }
            $lines[] = '    <div class="'.implode(' ', $containerQueryClasses).'"></div>';
            $lines[] = '    <div class="'.implode(' ', $standardClasses).'"></div>';
        }
        $lines[] = '';
    }

    protected function addColorsToComplete(array &$lines): void
    {
        $lines[] = '';
        $lines[] = '    {{-- Text color utilities --}}';
        $textColorClasses = [];
        foreach ($this->standardColors as $color => $shades) {
            foreach ($shades as $shade) {
                $textColorClasses[] = "text-{$color}-{$shade}";
            }
        }
        $lines[] = '    <div class="'.implode(' ', $textColorClasses).'"></div>';

        $lines[] = '';
        $lines[] = '    {{-- DaisyUI Theme Text Color Utilities --}}';
        $daisyTextColors = [];
        foreach ($this->daisyColors as $color) {
            $daisyTextColors[] = "text-{$color}";
        }
        $lines[] = '    <div class="'.implode(' ', $daisyTextColors).'"></div>';

        $lines[] = '';
        $lines[] = '    {{-- Background color utilities --}}';
        $bgColorClasses = [];
        foreach ($this->standardColors as $color => $shades) {
            foreach ($shades as $shade) {
                $bgColorClasses[] = "bg-{$color}-{$shade}";
            }
        }
        $lines[] = '    <div class="'.implode(' ', $bgColorClasses).'"></div>';

        $lines[] = '';
        $lines[] = '    {{-- DaisyUI Theme Background Color Utilities --}}';
        $daisyBgColors = [];
        foreach ($this->daisyColors as $color) {
            $daisyBgColors[] = "bg-{$color}";
        }
        $lines[] = '    <div class="'.implode(' ', $daisyBgColors).'"></div>';
        $lines[] = '';
    }

    protected function addOverflowToComplete(array &$lines): void
    {
        $overflowTypes = ['auto', 'scroll', 'visible', 'hidden'];
        $lines[] = '    <div class="items-start items-center items-end items-baseline items-stretch"></div>';
        $lines[] = '    <div class="justify-start justify-center justify-end justify-between justify-around justify-evenly justify-stretch"></div>';
        $lines[] = '    <div class="content-start content-center content-end content-baseline content-stretch"></div>';

        $overflowClasses = [];
        foreach ($overflowTypes as $type) {
            $overflowClasses[] = "overflow-{$type}";
        }
        $lines[] = '    <div class="'.implode(' ', $overflowClasses).'"></div>';

        $overflowXClasses = [];
        foreach ($overflowTypes as $type) {
            $overflowXClasses[] = "overflow-x-{$type}";
        }
        $lines[] = '    <div class="'.implode(' ', $overflowXClasses).'"></div>';

        $overflowYClasses = [];
        foreach ($overflowTypes as $type) {
            $overflowYClasses[] = "overflow-y-{$type}";
        }
        $lines[] = '    <div class="'.implode(' ', $overflowYClasses).'"></div>';
    }

    protected function addAlignmentToComplete(array &$lines): void
    {
        $lines[] = '    {{-- Layout classes --}}';
        $lines[] = '    <div class="container mx-auto md:container lg:container"></div>';
        $lines[] = '';
    }

    protected function addPaddingToComplete(array &$lines, array $breakpoints): void
    {
        // This would be too long for the complete file, so we'll add a comment
        $lines[] = '    {{-- Padding classes generated separately --}}';
        $lines[] = '';
    }

    protected function addMarginToComplete(array &$lines, array $breakpoints): void
    {
        // This would be too long for the complete file, so we'll add a comment
        $lines[] = '    {{-- Margin classes generated separately --}}';
        $lines[] = '';
    }

    protected function generateClassContent(
        string $prefix,
        array $standardClasses,
        array $specialClasses,
        array $fractionClasses,
        int $min,
        int $max,
        array $breakpoints
    ): string {
        $lines = [];

        // Add comment header
        $lines[] = "{{-- Auto-generated Tailwind safe classes for {$prefix} --}}";
        $lines[] = '{{-- Generated by: php scripts/generate_safe_classes.php --}}';
        $lines[] = '';

        // Standard classes without breakpoint
        $lines[] = "{{-- Standard {$prefix} classes --}}";
        $lines[] = $this->generateDivWithClasses($prefix, $standardClasses);

        if (! empty($specialClasses)) {
            $lines[] = $this->generateDivWithClasses($prefix, $specialClasses);
        }

        if (! empty($fractionClasses)) {
            $lines[] = $this->generateDivWithClasses($prefix, $fractionClasses);
        }

        $lines[] = '';

        // Responsive classes for each breakpoint
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive {$prefix} classes --}}";
            $lines[] = $this->generateDivWithClasses($prefix, $standardClasses, "@{$breakpoint}");

            if (! empty($specialClasses)) {
                $lines[] = $this->generateDivWithClasses($prefix, $specialClasses, "@{$breakpoint}");
            }

            if (! empty($fractionClasses)) {
                $lines[] = $this->generateDivWithClasses($prefix, $fractionClasses, "@{$breakpoint}");
            }

            $lines[] = '';
        }

        // Custom pixel values without breakpoint
        $lines[] = "{{-- Custom {$prefix} classes with arbitrary pixel values ({$min}px to {$max}px) --}}";
        $lines[] = $this->generatePixelClasses($prefix, $min, $max);
        $lines[] = '';

        // Custom pixel values with breakpoints
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive custom {$prefix} classes ({$min}px to {$max}px) --}}";
            $lines[] = $this->generatePixelClasses($prefix, $min, $max, "@{$breakpoint}");
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    protected function generateDivWithClasses(string $prefix, array $values, string $breakpoint = ''): string
    {
        $classes = [];
        foreach ($values as $value) {
            $class = $breakpoint ? "{$breakpoint}:{$prefix}-{$value}" : "{$prefix}-{$value}";
            $classes[] = $class;
        }

        return '<div class="'.implode(' ', $classes).'"></div>';
    }

    protected function generatePixelClasses(string $prefix, int $min, int $max, string $breakpoint = ''): string
    {
        $lines = [];

        // Group classes into chunks to avoid extremely long lines
        $chunkSize = 20;
        for ($i = $min; $i <= $max; $i += $chunkSize) {
            $classes = [];
            for ($j = $i; $j <= min($i + $chunkSize - 1, $max); $j++) {
                $class = $breakpoint ? "{$breakpoint}:{$prefix}-[{$j}px]" : "{$prefix}-[{$j}px]";
                $classes[] = $class;
            }
            $lines[] = '<div class="'.implode(' ', $classes).'"></div>';
        }

        return implode("\n", $lines);
    }

    protected function generateArbitraryMarginClasses(int $min, int $max, array $breakpoints): string
    {
        $lines = [];
        $lines[] = '';
        $lines[] = '{{-- Arbitrary value margin classes --}}';

        // Margin directions
        $directions = ['m', 'mt', 'mr', 'mb', 'ml', 'mx', 'my'];

        // Generate arbitrary value classes for custom spacing (chunked to avoid extremely long lines)
        $chunkSize = 20;
        for ($i = $min; $i <= $max; $i += $chunkSize) {
            $classes = [];
            for ($j = $i; $j <= min($i + $chunkSize - 1, $max); $j++) {
                foreach ($directions as $direction) {
                    // Positive classes
                    $classes[] = "{$direction}-[{$j}px]";
                    // Negative classes (except for 0)
                    if ($j > 0) {
                        $classes[] = "-{$direction}-[{$j}px]";
                    }
                }
            }
            $lines[] = '<div class="'.implode(' ', $classes).'"></div>';
        }
        $lines[] = '';

        // Responsive arbitrary value classes
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive arbitrary value margin classes --}}";

            for ($i = $min; $i <= $max; $i += $chunkSize) {
                $classes = [];
                for ($j = $i; $j <= min($i + $chunkSize - 1, $max); $j++) {
                    foreach ($directions as $direction) {
                        // Positive classes
                        $classes[] = "@{$breakpoint}:{$direction}-[{$j}px]";
                        // Negative classes (except for 0)
                        if ($j > 0) {
                            $classes[] = "@{$breakpoint}:-{$direction}-[{$j}px]";
                        }
                    }
                }
                $lines[] = '<div class="'.implode(' ', $classes).'"></div>';
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    protected function generateArbitraryPaddingClasses(int $min, int $max, array $breakpoints): string
    {
        $lines = [];
        $lines[] = '';
        $lines[] = '{{-- Arbitrary value padding classes --}}';

        // Padding directions
        $directions = ['p', 'pt', 'pr', 'pb', 'pl', 'px', 'py'];

        // Generate arbitrary value classes for custom spacing (chunked to avoid extremely long lines)
        $chunkSize = 20;
        for ($i = $min; $i <= $max; $i += $chunkSize) {
            $classes = [];
            for ($j = $i; $j <= min($i + $chunkSize - 1, $max); $j++) {
                foreach ($directions as $direction) {
                    $classes[] = "{$direction}-[{$j}px]";
                }
            }
            $lines[] = '<div class="'.implode(' ', $classes).'"></div>';
        }
        $lines[] = '';

        // Responsive arbitrary value classes
        foreach ($breakpoints as $breakpoint) {
            $breakpoint = trim($breakpoint);
            $lines[] = "{{-- @{$breakpoint} responsive arbitrary value padding classes --}}";

            for ($i = $min; $i <= $max; $i += $chunkSize) {
                $classes = [];
                for ($j = $i; $j <= min($i + $chunkSize - 1, $max); $j++) {
                    foreach ($directions as $direction) {
                        $classes[] = "@{$breakpoint}:{$direction}-[{$j}px]";
                    }
                }
                $lines[] = '<div class="'.implode(' ', $classes).'"></div>';
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}

// Parse command line arguments
function parseArguments(array $argv): array
{
    $options = [];

    foreach ($argv as $arg) {
        if (strpos($arg, '--') === 0) {
            if (strpos($arg, '=') !== false) {
                [$key, $value] = explode('=', substr($arg, 2), 2);
                $options[$key] = $value;
            } else {
                $options[substr($arg, 2)] = true;
            }
        }
    }

    return $options;
}

function showHelp(): void
{
    echo "Generate Tailwind CSS safe class lists for dynamic classes\n";
    echo "Usage: php generate_safe_classes.php [options]\n\n";
    echo "Options:\n";
    echo "  --type=TYPE       Type of classes to generate (all, height, min-height, width, min-width, padding, margin, grid, gap, flex, visibility, position, overflow, colors, shadows, alignment, complete) [default: all]\n";
    echo "  --min=MIN         Minimum pixel value [default: 1]\n";
    echo "  --max=MAX         Maximum pixel value [default: 500]\n";
    echo "  --breakpoints=BP  Comma-separated list of breakpoints [default: 3xl,5xl]\n";
    echo "  --output=DIR      Output directory [default: resources/views/dev]\n";
    echo "  --help            Show this help message\n";
}

// Main execution
$options = parseArguments($argv);

if (isset($options['help'])) {
    showHelp();
    exit(0);
}

try {
    $generator = new SafeClassGenerator;
    $generator->run($options);
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
    exit(1);
}
