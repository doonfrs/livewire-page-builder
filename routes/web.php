<?php

use Illuminate\Support\Facades\Route;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;

Route::middleware('web')->prefix('page-builder')->group(function () {
    Route::get(
        '/page/edit/{pageKey}/{pageTheme?}',
        PageEditor::class)->name('page-builder.page.edit'
        );
    Route::get(
        '/page/view/{pageKey}/{pageTheme?}',
        function ($pageKey, $pageTheme = null) {
            return app(PageBuilderRender::class)->renderPage($pageKey, $pageTheme);
        }
    )->name('page-builder.page.view');
});
