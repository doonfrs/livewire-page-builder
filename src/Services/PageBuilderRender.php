<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Trinavo\LivewirePageBuilder\Models\BuilderPage;

class PageBuilderRender
{
    public function renderPage($pageKey, $pageTheme = null)
    {
        $page = BuilderPage::where('key', $pageKey)->first();
        if (! $page) {
            return 'Page not found';
        }
        $rows = json_decode($page->components, true);

        return view('page-builder::view-page', [
            'pageKey' => $pageKey,
            'pageTheme' => $pageTheme,
            'rows' => $rows,
        ]);
    }
}
