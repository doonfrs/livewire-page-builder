# Performance

The page builder is split into two render pipelines so the editor's interactive overhead never reaches your visitors.

| Mode | Used by | Mechanism |
|---|---|---|
| **Builder** | `/page-builder/editor/...` | Livewire components, full interactivity, drag/drop, property panel |
| **View** | `/page-builder/page/view/...` and any custom render | A single Blade pass — no Livewire components, no Alpine state, no clipboard polling |

The view pipeline is what your end users hit. It reads the saved page JSON, generates the CSS classes and inline styles **once** on the server, and outputs plain HTML.

---

## The view pipeline

For a public page, the call chain is:

```
PageBuilderRender::renderPage($pageKey, $themeId)
    ↓
PageBuilderRender::parsePage()                    // load BuilderPage, decode components JSON
    ↓
PageBuilderRender::prepareRow()  / prepareBlock() // attach pre-computed cssClasses, inlineStyles,
    ↓                                             //   dataAttributes — no per-request recomputation
view('page-builder::view-page', [...])            // single Blade render, no Livewire
```

The relevant view files (publishable under `resources/views/vendor/page-builder/` via the `page-builder-views` tag):

- `view-page.blade.php` — the top-level public page wrapper
- `components/row-view.blade.php` — frontend row rendering
- `components/builder-page-block-view.blade.php` — page‑as‑block rendering

Each block's `render()` method still runs, but it returns plain Blade output — there's no Livewire root component wrapping it on the public site.

---

## What's *not* cached out of the box

The package does not ship with a built‑in HTTP or query cache for rendered pages. That keeps things simple and correct by default (no surprise stale content), and leaves caching policy to you.

Reasonable places to add caching, in order of bang‑for‑buck:

1. **Page response cache** — wrap your render route in `Cache::remember()` or a route‑level cache middleware. Pages change rarely; this is usually the biggest win.

   ```php
   Route::get('/{permalink}', function (string $permalink) {
       return Cache::remember("page:{$permalink}", now()->addHour(), function () use ($permalink) {
           return app(PageBuilderRender::class)->renderPage($permalink)->render();
       });
   });
   ```

   Invalidate on the `BuilderPageSaved` event so editor changes are reflected immediately:

   ```php
   Event::listen(
       \Trinavo\LivewirePageBuilder\Events\BuilderPageSaved::class,
       fn ($event) => Cache::forget("page:{$event->page->key}"),
   );
   ```

2. **`PageBuilderRender` result cache** — same idea but inside the render service, if you have many entry points to the same page. The `parsePage()` return value is fully serializable.

3. **Variables** — if a `PageBuilderVariables::register()` callable hits the database on every render, cache the result. Variables are resolved per request, not per page.

---

## Tailwind safe classes

Because page content lives in the database, Tailwind's static scanner can't see every class your editors might emit. Two mitigations are already in place:

- The package's `@source` directive (added by `pagebuilder:install` to your `resources/css/app.css`) makes Tailwind scan **all of the package's** Blade files, so any class the builder UI itself uses is preserved.
- For classes the builder generates dynamically from property values (responsive widths, spacing scales, gradient stops, …), the package ships `scripts/generate_safe_classes.php`. Run it during your build to produce a safelist that survives `tailwindcss --minify`.

---

## Tips for custom blocks

- **Minimize public properties.** Each `public $foo` is round‑tripped by Livewire on every update in the editor. Keep your editable surface tight.
- **Compute, don't store.** If a value is derived from other properties, expose it as a Livewire `#[Computed]` getter or a regular method called from your Blade view — don't persist it.
- **Avoid heavy work in `render()`.** It runs on every preview update and on every public page load. Expensive lookups (DB queries, HTTP calls) should be cached or moved to a `getXxxProperty()` computed.
- **Use `wire:key` inside loops** in editor Blade templates so Livewire's DOM diff stays cheap. Same advice as the [Livewire 3 performance guide](https://livewire.laravel.com/docs/computed-properties#performance-advantage).
- **Lazy‑load images.** The base `Block` class already exposes a `lazyLoad` shared property — wire it into your block's `<img loading="...">` attribute.

---

## See also

- [Custom Block Development](custom-block-development.md) — block rendering pipeline in detail
- [Advanced Configuration](advanced-configuration.md) — publishing views to customize the view pipeline
