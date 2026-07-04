<?php

use Illuminate\Support\Facades\Route;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;

$base = ['web', 'page-builder-localization'];

$editorMiddleware = array_merge(
    $base,
    config('page-builder.editor_middleware', ['auth'])
);

$renderMiddleware = array_merge(
    $base,
    config('page-builder.render_middleware', [])
);

Route::prefix('page-builder')->group(function () use ($editorMiddleware, $renderMiddleware) {

    Route::middleware($editorMiddleware)->group(function () {
        Route::get('/', function () {
            return redirect()->route('page-builder.themes');
        })->name('page-builder.index');

        Route::get('/themes', ThemeManager::class)->name('page-builder.themes');

        Route::get('/preview/cancel', function () {
            session()->forget('page_builder_preview_theme_id');

            return redirect('/')->with('notify', [
                'message' => __('Preview mode cancelled'),
                'type' => 'success',
            ]);
        })->name('page-builder.preview.cancel');

        Route::get(
            '/editor/{pageKey}/{themeId?}',
            PageEditor::class
        )->whereNumber('themeId')->name('page-builder.editor');

        Route::get(
            '/page/edit/{pageKey}/{pageTheme?}',
            function ($pageKey, $pageTheme = null) {
                if (is_numeric($pageTheme)) {
                    return redirect()->route('page-builder.editor', ['pageKey' => $pageKey, 'themeId' => $pageTheme]);
                }

                return redirect()->route('page-builder.themes');
            }
        )->name('page-builder.page.edit');
    });

    Route::middleware($renderMiddleware)->group(function () {
        Route::get(
            '/page/view/{pageKey}/{themeId?}',
            function ($pageKey, $themeId = null) {
                return app(PageBuilderRender::class)->renderPage($pageKey, $themeId);
            }
        )->name('page-builder.page.view');
    });
});
