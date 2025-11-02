<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Trinavo\LivewirePageBuilder\Blocks\RichText;

class QuillToTailwindConversionTest extends TestCase
{
    protected function getRichTextBlock(): RichText
    {
        return new RichText();
    }

    /**
     * Test that Quill alignment classes are converted to Tailwind classes
     */
    public function test_converts_quill_align_center_to_tailwind(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="ql-align-center">Centered text</p>';

        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        $this->assertStringContainsString('text-center', $result);
        $this->assertStringNotContainsString('ql-align-center', $result);
    }

    public function test_converts_quill_align_right_to_tailwind(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="ql-align-right">Right aligned text</p>';

        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        $this->assertStringContainsString('text-right', $result);
        $this->assertStringNotContainsString('ql-align-right', $result);
    }

    public function test_converts_quill_align_left_to_tailwind(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="ql-align-left">Left aligned text</p>';

        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        $this->assertStringContainsString('text-left', $result);
        $this->assertStringNotContainsString('ql-align-left', $result);
    }

    public function test_converts_quill_align_justify_to_tailwind(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="ql-align-justify">Justified text</p>';

        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        $this->assertStringContainsString('text-justify', $result);
        $this->assertStringNotContainsString('ql-align-justify', $result);
    }

    public function test_preserves_other_classes_when_converting(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="some-class ql-align-center another-class">Text</p>';

        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        $this->assertStringContainsString('some-class', $result);
        $this->assertStringContainsString('text-center', $result);
        $this->assertStringContainsString('another-class', $result);
        $this->assertStringNotContainsString('ql-align-center', $result);
    }

    public function test_handles_multiple_elements_with_quill_classes(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="ql-align-center">Centered</p><p class="ql-align-right">Right</p>';

        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        $this->assertStringContainsString('text-center', $result);
        $this->assertStringContainsString('text-right', $result);
        $this->assertStringNotContainsString('ql-align-center', $result);
        $this->assertStringNotContainsString('ql-align-right', $result);
    }

    public function test_cleans_up_extra_spaces_in_class_attributes(): void
    {
        $block = $this->getRichTextBlock();
        $html = '<p class="  some-class   ql-align-center   another-class  ">Text</p>';

        $reflection = new \ReflectionClass($block);
        $method = $reflection->getMethod('convertQuillClassesToTailwind');
        $method->setAccessible(true);

        $result = $method->invoke($block, $html);

        // Should not have multiple consecutive spaces
        $this->assertStringNotContainsString('  ', $result);
        $this->assertStringContainsString('text-center', $result);
    }
}
