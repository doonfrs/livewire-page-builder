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

        $rows = array_map([$this, 'prepareRow'], $rows);

        return view('page-builder::view-page', [
            'pageKey' => $pageKey,
            'pageTheme' => $pageTheme,
            'rows' => $rows,
        ]);
    }

    public function prepareRow($row)
    {
        $row['cssClasses'] = app(PageBuilderService::class)->getCssClassesFromProperties($row['properties'], true);
        $row['inlineStyles'] = app(PageBuilderService::class)->getInlineStylesFromProperties($row['properties']);

        $row['blocks'] = array_map([$this, 'prepareBlock'], $row['blocks']);

        return $row;
    }

    public function prepareBlock($block)
    {
        $block['cssClasses'] = app(PageBuilderService::class)->getCssClassesFromProperties($block['properties'], true);
        $block['inlineStyles'] = app(PageBuilderService::class)->getInlineStylesFromProperties($block['properties']);

        if ($block['alias'] == 'builder-page-block') {
            $page = BuilderPage::where('key', $block['properties']['blockPageName'])->first();
            if ($page) {
                $block['rows'] = json_decode($page->components, true);
                $block['rows'] = array_map([$this, 'prepareRow'], $block['rows']);
            }
        }

        return $block;
    }
}
