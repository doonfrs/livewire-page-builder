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
                abort(404, 'Theme not found. The theme with ID '.$themeId.' does not exist.');
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
                $rows = $this->prepareRows($rows, $this->rootLiveEditContext($pageKey, $themeId));
            }
        }

        return ['rows' => $rows ?? [], 'themeId' => $themeId];
    }

    /**
     * Prepare every row of a page, threading the live edit path down through the tree.
     *
     * @param  array|null  $context  Page-level live edit context, or null when live edit is off
     */
    public function prepareRows(array $rows, ?array $context = null): array
    {
        $prepared = [];

        foreach ($rows as $rowId => $row) {
            $prepared[$rowId] = $this->prepareRow($row, $this->childContext($context, (string) $rowId));
        }

        return $prepared;
    }

    /**
     * The live edit context a page's rows start from, or null when live edit is off.
     *
     * `path` is empty here; each row appends its own id, then each block appends its own.
     */
    public function rootLiveEditContext(?string $pageKey, $themeId): ?array
    {
        if (! $pageKey || ! app(PageBuilderUIService::class)->isLiveEditEnabled($pageKey, $themeId)) {
            return null;
        }

        return ['page' => $pageKey, 'theme' => $themeId, 'path' => []];
    }

    /**
     * Append one id to a live edit path. Null in, null out, so callers stay branch-free.
     */
    public function childContext(?array $context, string $id): ?array
    {
        if ($context === null) {
            return null;
        }

        $context['path'][] = $id;

        return $context;
    }

    public function prepareRow($row, ?array $liveEditContext = null)
    {
        $pageBuilderService = app(PageBuilderService::class);

        $row['cssClasses'] = $pageBuilderService->getCssClassesFromProperties($row['properties'], isRow: true);
        $row['inlineStyles'] = $pageBuilderService->getInlineStylesFromProperties($row['properties']);
        $row['dataAttributes'] = $pageBuilderService->getDataAttributesFromProperties($row['properties']);
        $row['rowCssClasses'] = $pageBuilderService->getRowCssClassesFromProperties($row['properties']);

        $blocks = [];
        foreach ($row['blocks'] as $blockId => $block) {
            $blocks[$blockId] = $this->prepareBlock($block, $this->childContext($liveEditContext, (string) $blockId));
        }
        $row['blocks'] = $blocks;

        return $row;
    }

    public function prepareBlock($block, ?array $liveEditContext = null)
    {
        $pageBuilderService = app(PageBuilderService::class);

        // Ensure editMode is false for frontend rendering
        $block['properties']['editMode'] = false;

        $block['cssClasses'] = $pageBuilderService->getCssClassesFromProperties($block['properties']);
        $block['inlineStyles'] = $pageBuilderService->getInlineStylesFromProperties($block['properties']);
        $block['dataAttributes'] = $pageBuilderService->getDataAttributesFromProperties($block['properties']);

        $block['component_exists'] = $pageBuilderService->isBlockAliasRegistered($block['alias'] ?? '');

        // Runtime-only, never persisted: where this block lives in builder_pages.components
        // and whether it opted into live editing.
        $block['liveEditContext'] = $liveEditContext;
        $block['liveEditable'] = $liveEditContext !== null
            && $pageBuilderService->blockHasLiveEditProperties($block['alias'] ?? null);

        if (! $block['component_exists']) {
            \Illuminate\Support\Facades\Log::warning('PageBuilderRender detected missing component during render', [
                'alias' => $block['alias'] ?? 'unknown',
            ]);
        }

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
                        // The embedded page's blocks live in their own BuilderPage row, so
                        // live edit starts a fresh context here instead of extending ours.
                        $block['rows'] = $this->prepareRows(
                            $block['rows'],
                            $this->rootLiveEditContext($blockPageName, $themeId)
                        );
                    }
                }
            }
        }

        // Handle nested blocks for row-block components
        if (isset($block['blocks']) && is_array($block['blocks']) && count($block['blocks']) > 0) {
            \Illuminate\Support\Facades\Log::debug('PageBuilderRender::prepareBlock processing nested blocks', [
                'alias' => $block['alias'],
                'nestedBlocksCount' => count($block['blocks']),
                'nestedBlocks' => $block['blocks'],
            ]);

            $nested = [];
            foreach ($block['blocks'] as $nestedId => $nestedBlock) {
                $nested[$nestedId] = $this->prepareBlock(
                    $nestedBlock,
                    $this->childContext($liveEditContext, (string) $nestedId)
                );
            }
            $block['blocks'] = $nested;
        }

        return $block;
    }
}
