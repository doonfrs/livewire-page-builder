<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
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
        return Str::headline($this->blockPageName);
    }
}
