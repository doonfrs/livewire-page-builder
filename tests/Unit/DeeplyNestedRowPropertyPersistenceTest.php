<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class DeeplyNestedRowPropertyPersistenceTest extends TestCase
{
    /** @test */
    public function can_update_property_on_third_level_nested_row(): void
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
                            'desktopHeight' => '',
                            'backgroundColor' => '',
                        ],
                        'blocks' => [
                            'level-3-deeply-nested-row' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => [
                                    'mobileWidth' => 'w-full',
                                    'tabletWidth' => 'w-full',
                                    'desktopWidth' => 'w-full',
                                    'desktopHeight' => '',
                                    'backgroundColor' => '',
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

        // Verify initial state - desktopHeight should be empty
        $this->assertEquals('', $pageEditor->rows['level-1-row']['blocks']['level-2-nested-row']['blocks']['level-3-deeply-nested-row']['properties']['desktopHeight']);

        // Update the desktopHeight property on the third-level nested row
        $pageEditor->updateBlockProperty('level-3-deeply-nested-row', null, 'desktopHeight', 'h-full');

        // Verify the property was updated
        $this->assertEquals('h-full', $pageEditor->rows['level-1-row']['blocks']['level-2-nested-row']['blocks']['level-3-deeply-nested-row']['properties']['desktopHeight']);
    }

    /** @test */
    public function can_update_background_color_on_third_level_nested_row(): void
    {
        $pageEditor = new PageEditor;

        // Set up three-level deep nested structure
        $pageEditor->rows = [
            'main-row' => [
                'blocks' => [
                    'nested-row-1' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [
                            'deeply-nested-target' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => [
                                    'mobileWidth' => 'w-full',
                                    'tabletWidth' => 'w-full',
                                    'desktopWidth' => 'w-full',
                                    'backgroundColor' => '',
                                ],
                                'blocks' => [],
                            ],
                        ],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Verify initial backgroundColor is empty
        $this->assertEquals('', $pageEditor->rows['main-row']['blocks']['nested-row-1']['blocks']['deeply-nested-target']['properties']['backgroundColor']);

        // Update the backgroundColor property on the deeply nested row
        $pageEditor->updateBlockProperty('deeply-nested-target', null, 'backgroundColor', '#ff0000');

        // Verify the property was updated
        $this->assertEquals('#ff0000', $pageEditor->rows['main-row']['blocks']['nested-row-1']['blocks']['deeply-nested-target']['properties']['backgroundColor']);
    }

    /** @test */
    public function can_update_multiple_properties_on_third_level_nested_row(): void
    {
        $pageEditor = new PageEditor;

        // Set up complex three-level structure
        $pageEditor->rows = [
            'container-row' => [
                'blocks' => [
                    'middle-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [
                            'inner-target-row' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => [
                                    'mobileWidth' => 'w-full',
                                    'tabletWidth' => 'w-full',
                                    'desktopWidth' => 'w-full',
                                    'desktopHeight' => '',
                                    'backgroundColor' => '',
                                    'mobilePaddingTop' => 0,
                                    'mobilePaddingBottom' => 0,
                                    'selfCentered' => false,
                                ],
                                'blocks' => [],
                            ],
                        ],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Update multiple properties on the deeply nested row
        $pageEditor->updateBlockProperty('inner-target-row', null, 'desktopHeight', 'h-screen');
        $pageEditor->updateBlockProperty('inner-target-row', null, 'backgroundColor', '#007acc');
        $pageEditor->updateBlockProperty('inner-target-row', null, 'mobilePaddingTop', 10);
        $pageEditor->updateBlockProperty('inner-target-row', null, 'mobilePaddingBottom', 20);
        $pageEditor->updateBlockProperty('inner-target-row', null, 'selfCentered', true);

        // Verify all properties were updated correctly
        $targetProperties = $pageEditor->rows['container-row']['blocks']['middle-row']['blocks']['inner-target-row']['properties'];

        $this->assertEquals('h-screen', $targetProperties['desktopHeight']);
        $this->assertEquals('#007acc', $targetProperties['backgroundColor']);
        $this->assertEquals(10, $targetProperties['mobilePaddingTop']);
        $this->assertEquals(20, $targetProperties['mobilePaddingBottom']);
        $this->assertTrue($targetProperties['selfCentered']);
    }

    /** @test */
    public function updates_only_target_row_in_complex_nested_structure(): void
    {
        $pageEditor = new PageEditor;

        // Create complex structure with multiple nested rows at same level
        $pageEditor->rows = [
            'root-row' => [
                'blocks' => [
                    'nested-row-a' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['backgroundColor' => '#000000'],
                        'blocks' => [
                            'deep-row-a1' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['backgroundColor' => '#111111'],
                                'blocks' => [],
                            ],
                            'deep-row-a2' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['backgroundColor' => '#222222'],
                                'blocks' => [],
                            ],
                        ],
                    ],
                    'nested-row-b' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['backgroundColor' => '#333333'],
                        'blocks' => [
                            'deep-row-target' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['backgroundColor' => '#444444'],
                                'blocks' => [],
                            ],
                        ],
                    ],
                ],
                'properties' => ['backgroundColor' => '#ffffff'],
            ],
        ];

        // Update only the target deep row
        $pageEditor->updateBlockProperty('deep-row-target', null, 'backgroundColor', '#ff5500');

        // Verify only the target was updated
        $this->assertEquals('#ff5500', $pageEditor->rows['root-row']['blocks']['nested-row-b']['blocks']['deep-row-target']['properties']['backgroundColor']);

        // Verify other rows were not affected
        $this->assertEquals('#000000', $pageEditor->rows['root-row']['blocks']['nested-row-a']['properties']['backgroundColor']);
        $this->assertEquals('#111111', $pageEditor->rows['root-row']['blocks']['nested-row-a']['blocks']['deep-row-a1']['properties']['backgroundColor']);
        $this->assertEquals('#222222', $pageEditor->rows['root-row']['blocks']['nested-row-a']['blocks']['deep-row-a2']['properties']['backgroundColor']);
        $this->assertEquals('#333333', $pageEditor->rows['root-row']['blocks']['nested-row-b']['properties']['backgroundColor']);
        $this->assertEquals('#ffffff', $pageEditor->rows['root-row']['properties']['backgroundColor']);
    }

    /** @test */
    public function reproduces_exact_user_scenario_property_update(): void
    {
        $pageEditor = new PageEditor;

        // Recreate the exact structure from the user's scenario where property updates were failing
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
                            // This represents the third level where property updates were failing
                            '68ced9f789abc' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => [
                                    'mobileWidth' => 'w-full',
                                    'tabletWidth' => 'w-full',
                                    'desktopWidth' => 'w-full',
                                    'desktopHeight' => '',
                                    'backgroundColor' => '',
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

        // Test the exact scenario: change height property to full
        $pageEditor->updateBlockProperty('68ced9f789abc', null, 'desktopHeight', 'h-full');

        // Verify the height property was updated
        $this->assertEquals('h-full', $pageEditor->rows['68ced970c6780']['blocks']['68ced9ad26e9d']['blocks']['68ced9f789abc']['properties']['desktopHeight']);

        // Test the exact scenario: change background color
        $pageEditor->updateBlockProperty('68ced9f789abc', null, 'backgroundColor', '#ff6600');

        // Verify the background color was updated
        $this->assertEquals('#ff6600', $pageEditor->rows['68ced970c6780']['blocks']['68ced9ad26e9d']['blocks']['68ced9f789abc']['properties']['backgroundColor']);

        // Verify parent properties were not affected
        $this->assertEquals('w-full', $pageEditor->rows['68ced970c6780']['blocks']['68ced9ad26e9d']['properties']['desktopWidth']);
        $this->assertEquals('w-full', $pageEditor->rows['68ced970c6780']['properties']['desktopWidth']);
    }

    /** @test */
    public function handles_four_level_deep_property_updates(): void
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
                                        'properties' => [
                                            'desktopWidth' => 'w-full',
                                            'backgroundColor' => '',
                                            'desktopHeight' => '',
                                        ],
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

        // Update properties on the level-4 nested row
        $pageEditor->updateBlockProperty('level-4-target', null, 'backgroundColor', '#purple');
        $pageEditor->updateBlockProperty('level-4-target', null, 'desktopHeight', 'h-96');

        // Verify property updates at level 4 work correctly
        $level4Properties = $pageEditor->rows['level-1']['blocks']['level-2']['blocks']['level-3']['blocks']['level-4-target']['properties'];
        $this->assertEquals('#purple', $level4Properties['backgroundColor']);
        $this->assertEquals('h-96', $level4Properties['desktopHeight']);

        // Verify all parent levels remain unchanged
        $this->assertEquals('w-full', $pageEditor->rows['level-1']['properties']['desktopWidth']);
        $this->assertEquals('w-full', $pageEditor->rows['level-1']['blocks']['level-2']['properties']['desktopWidth']);
        $this->assertEquals('w-full', $pageEditor->rows['level-1']['blocks']['level-2']['blocks']['level-3']['properties']['desktopWidth']);
    }

    /** @test */
    public function property_update_returns_false_for_nonexistent_row(): void
    {
        $pageEditor = new PageEditor;

        $pageEditor->rows = [
            'existing-row' => [
                'blocks' => [
                    'existing-nested' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['backgroundColor' => '#000000'],
                        'blocks' => [],
                    ],
                ],
                'properties' => ['backgroundColor' => '#ffffff'],
            ],
        ];

        // Try to update a property on a nonexistent row
        // Note: The method doesn't return a value, but we can verify the structure remains unchanged
        $originalStructure = $pageEditor->rows;

        $pageEditor->updateBlockProperty('nonexistent-row-id', null, 'backgroundColor', '#ff0000');

        // Verify the structure was not changed
        $this->assertEquals($originalStructure, $pageEditor->rows);
    }
}
