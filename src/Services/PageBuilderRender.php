<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;

class PageBuilderRender
{
    public function renderPage($pageKey, $themeId = null)
    {
        // If no themeId provided, try to get from session or default
        if (!$themeId) {
            $themeId = session('selected_theme_id') ?? session('default_theme_id');
        }

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
        $query = BuilderPage::where('key', $pageKey);
        
        if ($themeId) {
            $query->where('theme_id', $themeId);
        } else {
            // If no theme specified, get the first page with any theme or null theme
            $query->orderBy('theme_id');
        }
        
        $page = $query->first();
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
            // For page blocks, we need to consider the theme context
            $blockPageName = $block['properties']['blockPageName'] ?? null;
            $themeId = $block['properties']['themeId'] ?? session('selected_theme_id') ?? session('default_theme_id');
            
            if ($blockPageName) {
                $query = BuilderPage::where('key', $blockPageName);
                
                if ($themeId) {
                    $query->where('theme_id', $themeId);
                }
                
                $page = $query->first();
                
                if ($page) {
                    $block['rows'] = json_decode($page->components, true);
                    if ($block['rows']) {
                        $block['rows'] = array_map([$this, 'prepareRow'], $block['rows']);
                    }
                }
            }
        }

        return $block;
    }
}
