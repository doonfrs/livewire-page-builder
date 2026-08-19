<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class PageBuilderUIServiceLiveEditTest extends TestCase
{
    /** @test */
    public function live_edit_is_off_until_a_host_turns_it_on(): void
    {
        $this->assertFalse(app(PageBuilderUIService::class)->isLiveEditEnabled());
    }

    /** @test */
    public function it_can_be_enabled_with_a_static_flag(): void
    {
        $service = app(PageBuilderUIService::class);

        $this->assertSame($service, $service->enableLiveEdit());
        $this->assertTrue($service->isLiveEditEnabled());

        $service->enableLiveEdit(false);
        $this->assertFalse($service->isLiveEditEnabled());
    }

    /** @test */
    public function a_closure_is_resolved_on_every_check_not_memoised(): void
    {
        $calls = 0;
        app(PageBuilderUIService::class)->enableLiveEdit(function () use (&$calls) {
            $calls++;

            return $calls > 1;
        });

        // Memoising here would leak one request's permission decision into the next.
        $this->assertFalse(app(PageBuilderUIService::class)->isLiveEditEnabled());
        $this->assertTrue(app(PageBuilderUIService::class)->isLiveEditEnabled());
        $this->assertSame(2, $calls);
    }

    /** @test */
    public function the_closure_receives_the_page_key_and_theme_id(): void
    {
        $seen = [];
        app(PageBuilderUIService::class)->enableLiveEdit(function ($pageKey, $themeId) use (&$seen) {
            $seen = [$pageKey, $themeId];

            return $pageKey === 'home';
        });

        $this->assertTrue(app(PageBuilderUIService::class)->isLiveEditEnabled('home', 7));
        $this->assertSame(['home', 7], $seen);

        $this->assertFalse(app(PageBuilderUIService::class)->isLiveEditEnabled('header', 7));
    }

    /** @test */
    public function a_zero_argument_closure_still_works(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(fn () => true);

        $this->assertTrue(app(PageBuilderUIService::class)->isLiveEditEnabled('home', 1));
    }

    /** @test */
    public function the_toggle_expression_is_empty_until_set(): void
    {
        $service = app(PageBuilderUIService::class);

        $this->assertSame('', $service->getLiveEditToggleExpression());

        $service->setLiveEditToggleExpression('$store.adminEdit?.on');
        $this->assertSame('$store.adminEdit?.on', $service->getLiveEditToggleExpression());
    }

    /** @test */
    public function clear_resets_the_live_edit_settings(): void
    {
        $service = app(PageBuilderUIService::class)
            ->enableLiveEdit(true)
            ->setLiveEditToggleExpression('$store.adminEdit?.on');

        $service->clear();

        $this->assertFalse($service->isLiveEditEnabled());
        $this->assertSame('', $service->getLiveEditToggleExpression());
    }
}
