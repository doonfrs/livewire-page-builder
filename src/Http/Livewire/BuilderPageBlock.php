<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\ThemeResolver;

class BuilderPageBlock extends Block
{
    use ThemeResolver;

    public ?string $blockPageName = null;

    public ?array $rows = null;

    public ?array $properties = null;

    public ?BuilderPage $page = null;

    /**
     * Theme actually used to load $rows. Live edit addressing must quote this exact
     * value, not re-resolve it, or a save could target a different theme's page.
     */
    public $resolvedThemeId = null;

    public $mobileWidth = 'w-full';

    public $tabletWidth = 'w-full';

    public $desktopWidth = 'w-full';

    public function mount()
    {
        $themeId = $this->resolveThemeId();
        $this->resolvedThemeId = $themeId;

        $query = BuilderPage::query()->where('key', $this->blockPageName);
        $query->where('theme_id', $themeId);

        $this->page = $query->first();
        if (! $this->page) {
            return 'Page not found';
        }

        $this->rows = $this->page->components;
    }

    public function render()
    {
        // The embedded page's blocks belong to their own BuilderPage row, so live edit
        // starts a fresh context here rather than extending the host page's path.
        $render = app(PageBuilderRender::class);
        $service = app(PageBuilderService::class);
        $root = $render->rootLiveEditContext($this->blockPageName, $this->resolvedThemeId);

        $contexts = [];
        $gears = [];

        if ($root !== null) {
            foreach ($this->rows ?? [] as $rowId => $row) {
                $rowContext = $render->childContext($root, (string) $rowId);

                foreach ($row['blocks'] ?? [] as $blockId => $block) {
                    $context = $render->childContext($rowContext, (string) $blockId);
                    $contexts[$rowId][$blockId] = $context;
                    $gears[$rowId][$blockId] = $service->blockHasLiveEditProperties($block['alias'] ?? null)
                        ? $context
                        : null;
                }
            }
        }

        return view('page-builder::livewire.builder.builder-page-block', [
            'blockPageName' => $this->blockPageName,
            'liveEditContexts' => $contexts,
            'liveEditGears' => $gears,
        ]);
    }

    public function getPageBuilderLabel(): string
    {
        foreach (app(PageBuilderService::class)->getConfigBlocksPages() as $blockName => $blockInfo) {
            if (is_int($blockName)) {
                continue;
            }
            if ($blockName === $this->blockPageName) {
                if (isset($blockInfo['label'])) {
                    return __($blockInfo['label']);
                }
            }
        }

        $fallbackLabel = Str::headline($this->blockPageName);

        return __($fallbackLabel);
    }
}
