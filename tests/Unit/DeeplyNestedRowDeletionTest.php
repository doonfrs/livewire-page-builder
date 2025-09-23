<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class DeeplyNestedRowDeletionTest extends TestCase
{
    /** @test */
    public function can_delete_three_level_deep_nested_row(): void
    {
        $pageEditor = new PageEditor;

        // Set up three-level deep nested structure: Row => nested row => nested row
        $pageEditor->rows = [
            'level-1-row' => [
                'blocks' => [
                    'level-2-nested-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => [
                            'mobileWidth' => 'w-full',
                            'tabletWidth' => 'w-full',
                            'desktopWidth' => 'w-full',
                        ],
                        'blocks' => [
                            'level-3-deeply-nested-row' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => [
                                    'mobileWidth' => 'w-full',
                                    'tabletWidth' => 'w-full',
                                    'desktopWidth' => 'w-full',
                                    'backgroundPosition' => 'center',
                                    'backgroundSize' => 'cover',
                                    'backgroundRepeat' => 'no-repeat',
                                    'selfCentered' => true,
                                    'flex' => 'row',
                                ],
                                'blocks' => [],
                            ],
                        ],
                    ],
                ],
                'properties' => [
                    'mobileWidth' => 'w-full',
                    'tabletWidth' => 'w-full',
                    'desktopWidth' => 'w-full',
                ],
            ],
        ];

        // Verify initial structure is correct
        $this->assertArrayHasKey('level-1-row', $pageEditor->rows);
        $this->assertArrayHasKey('level-2-nested-row', $pageEditor->rows['level-1-row']['blocks']);
        $this->assertArrayHasKey('level-3-deeply-nested-row', $pageEditor->rows['level-1-row']['blocks']['level-2-nested-row']['blocks']);

        // Delete the deeply nested row (level 3)
        $pageEditor->deleteRow('level-3-deeply-nested-row');

        // Verify the deeply nested row was deleted
        $this->assertArrayNotHasKey(
            'level-3-deeply-nested-row',
            $pageEditor->rows['level-1-row']['blocks']['level-2-nested-row']['blocks'],
            'The deeply nested row should be deleted'
        );

        // Verify the parent structure is still intact
        $this->assertArrayHasKey('level-1-row', $pageEditor->rows, 'Top-level row should remain');
        $this->assertArrayHasKey('level-2-nested-row', $pageEditor->rows['level-1-row']['blocks'], 'Middle-level nested row should remain');
        $this->assertEmpty($pageEditor->rows['level-1-row']['blocks']['level-2-nested-row']['blocks'], 'Level-2 blocks should be empty after deletion');
    }

    /** @test */
    public function can_delete_middle_level_nested_row_in_three_level_structure(): void
    {
        $pageEditor = new PageEditor;

        // Set up three-level deep nested structure with content at level 3
        $pageEditor->rows = [
            'top-row' => [
                'blocks' => [
                    'middle-nested-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [
                            'deep-nested-row' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['desktopWidth' => 'w-1/2'],
                                'blocks' => [],
                            ],
                        ],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Delete the middle-level nested row (this should also delete its children)
        $pageEditor->deleteRow('middle-nested-row');

        // Verify the middle row and its child are deleted
        $this->assertEmpty($pageEditor->rows['top-row']['blocks'], 'Top-level blocks should be empty');
        $this->assertArrayHasKey('top-row', $pageEditor->rows, 'Top-level row should remain');
    }

    /** @test */
    public function can_delete_deeply_nested_row_with_complex_sibling_structure(): void
    {
        $pageEditor = new PageEditor;

        // Create a complex structure with multiple nested levels and siblings
        $pageEditor->rows = [
            'main-row' => [
                'blocks' => [
                    'regular-block' => [
                        'alias' => 'some-regular-block',
                        'properties' => ['content' => 'Regular content'],
                    ],
                    'nested-row-1' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [
                            'deeply-nested-target' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['desktopWidth' => 'w-full'],
                                'blocks' => [],
                            ],
                            'deeply-nested-sibling' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['desktopWidth' => 'w-full'],
                                'blocks' => [],
                            ],
                        ],
                    ],
                    'nested-row-2' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Delete only the target deeply nested row
        $pageEditor->deleteRow('deeply-nested-target');

        // Verify only the target was deleted, siblings remain
        $nestedRow1Blocks = $pageEditor->rows['main-row']['blocks']['nested-row-1']['blocks'];
        $this->assertArrayNotHasKey('deeply-nested-target', $nestedRow1Blocks, 'Target deeply nested row should be deleted');
        $this->assertArrayHasKey('deeply-nested-sibling', $nestedRow1Blocks, 'Sibling deeply nested row should remain');

        // Verify all other structure remains intact
        $this->assertArrayHasKey('regular-block', $pageEditor->rows['main-row']['blocks'], 'Regular block should remain');
        $this->assertArrayHasKey('nested-row-1', $pageEditor->rows['main-row']['blocks'], 'Parent nested row should remain');
        $this->assertArrayHasKey('nested-row-2', $pageEditor->rows['main-row']['blocks'], 'Sibling nested row should remain');
    }

    /** @test */
    public function reproduces_exact_user_scenario_from_logs(): void
    {
        $pageEditor = new PageEditor;

        // Recreate the exact structure from the user's scenario that initially failed
        $pageEditor->rows = [
            '68ced970c6780' => [
                'blocks' => [
                    '68ced9ad26e9d' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => [
                            'mobileWidth' => 'w-full',
                            'tabletWidth' => 'w-full',
                            'desktopWidth' => 'w-full',
                            'backgroundPosition' => 'center',
                            'backgroundSize' => 'cover',
                            'backgroundRepeat' => 'no-repeat',
                            'selfCentered' => true,
                            'flex' => 'row',
                            'contentWidthMobile' => 'w-full',
                            'contentWidthTablet' => 'w-full',
                            'contentWidthDesktop' => 'w-full',
                        ],
                        'blocks' => [
                            // This represents the third level that was causing the issue
                            '68ced9f123456' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => [
                                    'mobileWidth' => 'w-full',
                                    'tabletWidth' => 'w-full',
                                    'desktopWidth' => 'w-full',
                                ],
                                'blocks' => [],
                            ],
                        ],
                    ],
                ],
                'properties' => [
                    'mobileWidth' => 'w-full',
                    'tabletWidth' => 'w-full',
                    'desktopWidth' => 'w-full',
                    'backgroundPosition' => 'center',
                    'backgroundSize' => 'cover',
                    'backgroundRepeat' => 'no-repeat',
                    'selfCentered' => true,
                    'flex' => 'row',
                    'contentWidthMobile' => 'w-full',
                    'contentWidthTablet' => 'w-full',
                    'contentWidthDesktop' => 'w-full',
                ],
            ],
        ];

        // Attempt to delete the deeply nested row (this was failing before the fix)
        $pageEditor->deleteRow('68ced9f123456');

        // Verify the deletion worked
        $this->assertArrayNotHasKey(
            '68ced9f123456',
            $pageEditor->rows['68ced970c6780']['blocks']['68ced9ad26e9d']['blocks'],
            'The deeply nested row should be successfully deleted'
        );

        // Verify parent structure remains intact
        $this->assertArrayHasKey('68ced970c6780', $pageEditor->rows, 'Top-level row should remain');
        $this->assertArrayHasKey('68ced9ad26e9d', $pageEditor->rows['68ced970c6780']['blocks'], 'Middle-level row should remain');
        $this->assertEmpty($pageEditor->rows['68ced970c6780']['blocks']['68ced9ad26e9d']['blocks'], 'Deep blocks should be empty after deletion');
    }

    /** @test */
    public function handles_four_level_deep_nesting(): void
    {
        $pageEditor = new PageEditor;

        // Test even deeper nesting to ensure robustness
        $pageEditor->rows = [
            'level-1' => [
                'blocks' => [
                    'level-2' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [
                            'level-3' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['desktopWidth' => 'w-full'],
                                'blocks' => [
                                    'level-4-target' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                        'properties' => ['desktopWidth' => 'w-full'],
                                        'blocks' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Delete the level-4 nested row
        $pageEditor->deleteRow('level-4-target');

        // Verify deletion at level 4 works
        $level3Blocks = $pageEditor->rows['level-1']['blocks']['level-2']['blocks']['level-3']['blocks'];
        $this->assertEmpty($level3Blocks, 'Level-4 row should be deleted');

        // Verify all parent levels remain
        $this->assertArrayHasKey('level-1', $pageEditor->rows);
        $this->assertArrayHasKey('level-2', $pageEditor->rows['level-1']['blocks']);
        $this->assertArrayHasKey('level-3', $pageEditor->rows['level-1']['blocks']['level-2']['blocks']);
    }
}
