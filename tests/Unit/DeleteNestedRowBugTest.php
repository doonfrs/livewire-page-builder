<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class DeleteNestedRowBugTest extends TestCase
{
    /** @test */
    public function deleteRow_does_not_handle_nested_rows(): void
    {
        // Create a PageEditor instance manually
        $pageEditor = new PageEditor();

        // Set up the exact structure from the logs
        $pageEditor->rows = [
            '68ced970c6780' => [
                'blocks' => [
                    '68ced9ad26e9d' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => [
                            'mobileWidth' => 'w-full',
                            'tabletWidth' => 'w-full',
                            'desktopWidth' => 'w-full',
                        ],
                        'blocks' => [],
                    ]
                ],
                'properties' => [
                    'mobileWidth' => 'w-full',
                    'tabletWidth' => 'w-full',
                    'desktopWidth' => 'w-full',
                ]
            ]
        ];

        // Verify initial state
        $this->assertArrayHasKey('68ced970c6780', $pageEditor->rows);
        $this->assertArrayHasKey('68ced9ad26e9d', $pageEditor->rows['68ced970c6780']['blocks']);

        // Try to delete the nested row (this should work now)
        $pageEditor->deleteRow('68ced9ad26e9d');

        // FIXED: The nested row should now be removed
        $this->assertArrayNotHasKey('68ced9ad26e9d', $pageEditor->rows['68ced970c6780']['blocks'],
            'FIXED: Nested row should be deleted');
    }

    /** @test */
    public function deleteRow_works_for_top_level_rows(): void
    {
        // Create a PageEditor instance manually
        $pageEditor = new PageEditor();

        // Set up structure with top-level rows
        $pageEditor->rows = [
            'top-level-1' => [
                'blocks' => [],
                'properties' => ['desktopWidth' => 'w-full']
            ],
            'top-level-2' => [
                'blocks' => [],
                'properties' => ['desktopWidth' => 'w-1/2']
            ]
        ];

        // Verify initial state
        $this->assertCount(2, $pageEditor->rows);
        $this->assertArrayHasKey('top-level-1', $pageEditor->rows);

        // Delete a top-level row (this should work)
        $pageEditor->deleteRow('top-level-1');

        // Top-level row deletion works correctly
        $this->assertCount(1, $pageEditor->rows);
        $this->assertArrayNotHasKey('top-level-1', $pageEditor->rows,
            'Top-level row should be deleted');
        $this->assertArrayHasKey('top-level-2', $pageEditor->rows,
            'Other top-level row should remain');
    }

    /** @test */
    public function demonstrate_the_bug_with_exact_log_structure(): void
    {
        $pageEditor = new PageEditor();

        // This is the exact structure from the user's logs
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
                            'contentCentered' => true,
                            'contentWidthMobile' => 'w-full',
                            'contentWidthTablet' => 'w-full',
                            'contentWidthDesktop' => 'w-full',
                        ],
                        'blocks' => [],
                    ]
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
                    'contentCentered' => true,
                    'contentWidthMobile' => 'w-full',
                    'contentWidthTablet' => 'w-full',
                    'contentWidthDesktop' => 'w-full',
                ]
            ]
        ];

        // The user tries to delete the nested row '68ced9ad26e9d'
        $pageEditor->deleteRow('68ced9ad26e9d');

        // This should now work: the nested row should be deleted
        $this->assertFalse(
            isset($pageEditor->rows['68ced970c6780']['blocks']['68ced9ad26e9d']),
            'FIXED: The nested row 68ced9ad26e9d should be deleted and no longer exist'
        );
    }
}