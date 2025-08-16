<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\ThemeResolver;

class BuilderPageBlock extends Block
{
    use ThemeResolver;

    public ?string $blockPageName = null;

    public ?array $rows;

    public ?array $properties;

    public ?BuilderPage $page;

    public $mobileWidth = 'w-full';

    public $tabletWidth = 'w-full';

    public $desktopWidth = 'w-full';

    public function mount()
    {
        $themeId = $this->resolveThemeId();

        $query = BuilderPage::where('key', $this->blockPageName);
        $query->where('theme_id', $themeId);

        $this->page = $query->first();
        if (! $this->page) {
            return 'Page not found';
        }

        $this->rows = $this->page->components;
    }

    public function render()
    {
        return view('page-builder::livewire.builder.builder-page-block', [
            'blockPageName' => $this->blockPageName,
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

        return __(Str::headline($this->blockPageName));
    }
}
