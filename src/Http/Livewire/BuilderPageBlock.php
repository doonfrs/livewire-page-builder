<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Block;

class BuilderPageBlock extends Block
{
    public ?string $blockPageName;

    public ?array $rows;

    public ?array $properties;

    public ?BuilderPage $page;

    public function mount()
    {

        $this->page = BuilderPage::where('key', $this->blockPageName)->first();
        if (! $this->page) {
            return 'Page not found';
        }
        $this->rows = json_decode($this->page->components, true);

    }

    public function render()
    {
        return view('page-builder::builder.builder-page-block', [
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
                    if (is_callable($blockInfo['label'])) {
                        return $blockInfo['label']();
                    }

                    return $blockInfo['label'];
                }
            }
        }

        return Str::headline($this->blockPageName);
    }
}
