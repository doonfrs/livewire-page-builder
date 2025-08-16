<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Support\ThemeResolver;

class PageBuilderRender
{
    use ThemeResolver;

    public function renderPage($pageKey, $themeId = null)
    {
        $themeId = $this->resolveThemeId($themeId);
        $page = $this->parsePage($pageKey, $themeId);
        $theme = $themeId ? Theme::find($themeId) : null;

        return view('page-builder::view-page', [
            'pageKey' => $pageKey,
            'themeId' => $themeId,
            'theme' => $theme,
            'rows' => $page['rows'],
        ]);
    }

    public function parsePage($pageKey, $themeId = null)
    {
        $themeId = $this->resolveThemeId($themeId);
        $query = BuilderPage::where('key', $pageKey);
        $query->where('theme_id', $themeId);

        $page = $query->first();
        $rows = [];

        if ($page) {
            $rows = $page->components;

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
            // For page blocks, we need to consider the theme context
            $blockPageName = $block['properties']['blockPageName'] ?? null;
            $themeId = $block['properties']['themeId'] ?? $this->resolveThemeId();

            if ($blockPageName) {
                $query = BuilderPage::where('key', $blockPageName);
                $query->where('theme_id', $themeId);

                $page = $query->first();

                if ($page) {
                    $block['rows'] = $page->components;
                    if ($block['rows']) {
                        $block['rows'] = array_map([$this, 'prepareRow'], $block['rows']);
                    }
                }
            }
        }

        return $block;
    }
}
