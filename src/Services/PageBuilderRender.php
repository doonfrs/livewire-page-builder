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

        // Validate theme exists if ID is provided
        if ($themeId) {
            $theme = Theme::find($themeId);
            if (! $theme) {
                abort(404, 'Theme not found. The theme with ID ' . $themeId . ' does not exist.');
            }
        } else {
            $theme = null;
        }

        $page = $this->parsePage($pageKey, $themeId);

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
        $row['cssClasses'] = app(PageBuilderService::class)->getCssClassesFromProperties($row['properties']);
        $row['inlineStyles'] = app(PageBuilderService::class)->getInlineStylesFromProperties($row['properties']);

        $row['blocks'] = array_map([$this, 'prepareBlock'], $row['blocks']);

        $row['rowCssClasses'] = app(PageBuilderService::class)->getRowCssClassesFromProperties($row['properties']);

        return $row;
    }

    public function prepareBlock($block)
    {
        $block['cssClasses'] = app(PageBuilderService::class)->getCssClassesFromProperties($block['properties']);
        $block['inlineStyles'] = app(PageBuilderService::class)->getInlineStylesFromProperties($block['properties']);

        \Illuminate\Support\Facades\Log::info('PageBuilderRender::prepareBlock called', [
            'alias' => $block['alias'],
            'hasBlocks' => isset($block['blocks']),
            'blocksCount' => isset($block['blocks']) ? count($block['blocks']) : 0,
        ]);

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

        // Handle nested blocks for row-block components
        if (isset($block['blocks']) && is_array($block['blocks']) && count($block['blocks']) > 0) {
            \Illuminate\Support\Facades\Log::info('PageBuilderRender::prepareBlock processing nested blocks', [
                'alias' => $block['alias'],
                'nestedBlocksCount' => count($block['blocks']),
                'nestedBlocks' => $block['blocks'],
            ]);

            $block['blocks'] = array_map([$this, 'prepareBlock'], $block['blocks']);
        }

        return $block;
    }
}
