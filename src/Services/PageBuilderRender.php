<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Trinavo\LivewirePageBuilder\Models\BuilderPage;

class PageBuilderRender
{
    public function renderPage($pageKey, $pageTheme = null)
    {
        $page = $this->parsePage($pageKey);

        return view('page-builder::view-page', [
            'pageKey' => $pageKey,
            'pageTheme' => $pageTheme,
            'rows' => $page['rows'],
        ]);
    }

    public function parsePage($pageKey)
    {
        $page = BuilderPage::where('key', $pageKey)->first();
        $rows = [];
        if ($page) {
            $rows = json_decode($page->components, true);

            if ($rows) {
                $rows = array_map([$this, 'prepareRow'], $rows);
            }
        }

        return ['rows' => $rows ?? []];
    }

    public function prepareRow($row)
    {
        $row['cssClasses'] = app(PageBuilderService::class)->getCssClassesFromProperties($row['properties'], true);
        $row['inlineStyles'] = app(PageBuilderService::class)->getInlineStylesFromProperties($row['properties']);

        $row['blocks'] = array_map([$this, 'prepareBlock'], $row['blocks']);

        $row['rowCssClasses'] = app(PageBuilderService::class)->getRowCssClassesFromProperties($row['properties']);

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
