<?php

use Illuminate\Support\Facades\Route;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;

Route::middleware('web')->prefix('page-builder')->group(function () {
    Route::get('/page/{pageId}', PageEditor::class)->name('page-builder.page.view');
});
