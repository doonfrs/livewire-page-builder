<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ThemeCloneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_clones_a_theme_and_reports_success(): void
    {
        $theme = Theme::create([
            'name' => 'Source Theme',
            'description' => 'Original',
        ]);

        Livewire::test(ThemeManager::class)
            ->call('openCloneModal', $theme->id)
            ->call('cloneTheme')
            // Under the bug, building the success message dereferenced a nulled
            // $themeToClone, the ErrorException was swallowed and a failure toast
            // was dispatched instead. Asserting success here proves the regression.
            ->assertDispatched('notify', type: 'success');

        // The clone modal state is reset after a successful clone.
        $this->assertDatabaseHas('builder_themes', [
            'name' => __('Copy of').' Source Theme',
        ]);
    }
}
