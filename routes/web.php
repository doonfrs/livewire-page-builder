<?php

use Illuminate\Support\Facades\Route;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;

$middleware = array_merge(
    ['web', 'page-builder-localization'],
    config('page-builder.middleware', [])
);

Route::middleware($middleware)->prefix('page-builder')->group(function () {
    // Theme Management Routes
    Route::get('/themes', ThemeManager::class)->name('page-builder.themes');
    
    // Page Builder Routes - Updated to use theme IDs
    Route::get(
        '/editor/{pageKey}/{themeId?}',
        PageEditor::class
    )->name('page-builder.editor');
    
    Route::get(
        '/page/view/{pageKey}/{themeId?}',
        function ($pageKey, $themeId = null) {
            return app(PageBuilderRender::class)->renderPage($pageKey, $themeId);
        }
    )->name('page-builder.page.view');
    
    // Backward compatibility routes (deprecated)
    Route::get(
        '/page/edit/{pageKey}/{pageTheme?}',
        function ($pageKey, $pageTheme = null) {
            // If pageTheme is numeric, treat as theme ID, otherwise redirect to theme manager
            if (is_numeric($pageTheme)) {
                return redirect()->route('page-builder.editor', ['pageKey' => $pageKey, 'themeId' => $pageTheme]);
            }
            return redirect()->route('page-builder.themes');
        }
    )->name('page-builder.page.edit');
});
