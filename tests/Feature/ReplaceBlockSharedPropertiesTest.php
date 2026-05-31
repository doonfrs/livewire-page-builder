<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ReplaceBlockSharedPropertiesTest extends TestCase
{
    protected string $richTextAlias = 'page-builder-trinavo-livewire-page-builder-blocks-rich-text';

    protected string $spacerAlias = 'page-builder-trinavo-livewire-page-builder-blocks-spacer';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('page-builder.blocks', [
            \Trinavo\LivewirePageBuilder\Blocks\RichText::class,
            \Trinavo\LivewirePageBuilder\Blocks\Spacer::class,
        ]);
        config()->set('page-builder.pages', []);
    }

    /** @test */
    public function replacing_a_block_carries_over_shared_layout_properties(): void
    {
        // A block sized differently from the defaults, plus a block-specific value.
        $oldBlockId = 'old-block-id';
        $blocks = [
            $oldBlockId => [
                'alias' => $this->richTextAlias,
                'properties' => [
                    'desktopWidth' => 'w-5xl',     // shared (non-default)
                    'desktopHeight' => 'h-[400px]', // shared (non-default)
                    'desktopPaddingTop' => '7',     // shared (non-default)
                    'content' => 'block specific content', // NOT shared
                ],
            ],
        ];

        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row',
            'properties' => [],
            'blocks' => $blocks,
            'editMode' => true,
            'isNested' => true,
        ]);

        $component->call(
            'addBlockToNestedRow',
            rowId: 'test-row',
            blockAlias: $this->spacerAlias,
            replaceBlockId: $oldBlockId,
        );

        $resultBlocks = $component->get('blocks');

        // Still exactly one block, swapped in place.
        $this->assertCount(1, $resultBlocks, 'Replace should keep a single block');

        // The old id is gone (a fresh uuid is generated for the replacement).
        $this->assertArrayNotHasKey($oldBlockId, $resultBlocks, 'Old block id should be replaced');

        $newBlock = reset($resultBlocks);

        // The alias changed to the new block type.
        $this->assertSame($this->spacerAlias, $newBlock['alias'], 'New block should be the chosen type');

        // Shared layout properties are carried over from the replaced block.
        $this->assertSame('w-5xl', $newBlock['properties']['desktopWidth'], 'Width should carry over');
        $this->assertSame('h-[400px]', $newBlock['properties']['desktopHeight'], 'Height should carry over');
        $this->assertSame('7', $newBlock['properties']['desktopPaddingTop'], 'Padding should carry over');

        // Block-specific content of the replaced block is dropped.
        $this->assertArrayNotHasKey('content', $newBlock['properties'], 'Block-specific content should not carry over');
    }

    /** @test */
    public function replacing_a_block_keeps_new_block_defaults_for_unset_shared_properties(): void
    {
        // Old block only overrides desktopWidth; everything else should fall back
        // to the NEW block's defaults rather than being empty.
        $oldBlockId = 'old-block-id';
        $blocks = [
            $oldBlockId => [
                'alias' => $this->richTextAlias,
                'properties' => [
                    'desktopWidth' => 'w-5xl',
                ],
            ],
        ];

        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row',
            'properties' => [],
            'blocks' => $blocks,
            'editMode' => true,
            'isNested' => true,
        ]);

        $component->call(
            'addBlockToNestedRow',
            rowId: 'test-row',
            blockAlias: $this->spacerAlias,
            replaceBlockId: $oldBlockId,
        );

        $resultBlocks = $component->get('blocks');
        $newBlock = reset($resultBlocks);

        // Carried shared value.
        $this->assertSame('w-5xl', $newBlock['properties']['desktopWidth']);

        // Defaults of the new (Spacer) block are present — not an empty array.
        $spacerClass = app(PageBuilderService::class)->getClassNameFromAlias($this->spacerAlias);
        $expectedDefaults = app($spacerClass)->getPropertyValues();
        $this->assertNotEmpty($newBlock['properties'], 'Replacement should not have empty properties');
        $this->assertArrayHasKey('mobileWidth', $newBlock['properties'], 'New block should keep its default shared properties');
        $this->assertSame($expectedDefaults['mobileWidth'], $newBlock['properties']['mobileWidth']);
    }
}
