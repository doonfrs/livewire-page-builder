<?php

/**
 * Generate Tailwind CSS safe class lists for dynamic classes
 * Usage: php generate_safe_classes.php [options]
 *
 * Options:
 *   --type=TYPE       Type of classes to generate (all, height, min-height, width, min-width) [default: all]
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
    ];

    protected array $fractionWidthClasses = [
        '1/2', '1/3', '2/3', '1/4', '2/4', '3/4', '1/5', '2/5', '3/5', '4/5',
        '1/6', '2/6', '3/6', '4/6', '5/6', '1/12', '2/12', '3/12', '4/12', '5/12',
        '6/12', '7/12', '8/12', '9/12', '10/12', '11/12',
    ];

    public function run(array $options = []): void
    {
        $type = $options['type'] ?? 'all';
        $min = (int) ($options['min'] ?? 1);
        $max = (int) ($options['max'] ?? 500);
        $breakpoints = explode(',', $options['breakpoints'] ?? 'xl,3xl,5xl');
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
            case 'all':
                $this->generateHeightClasses($outputDir, $min, $max, $breakpoints);
                $this->generateMinHeightClasses($outputDir, $min, $max, $breakpoints);
                $this->generateWidthClasses($outputDir, $min, $max, $breakpoints);
                $this->generateMinWidthClasses($outputDir, $min, $max, $breakpoints);
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
    echo "  --type=TYPE       Type of classes to generate (all, height, min-height, width, min-width) [default: all]\n";
    echo "  --min=MIN         Minimum pixel value [default: 1]\n";
    echo "  --max=MAX         Maximum pixel value [default: 500]\n";
    echo "  --breakpoints=BP  Comma-separated list of breakpoints [default: xl,3xl,5xl]\n";
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
