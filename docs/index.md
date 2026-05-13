# Livewire Page Builder — Documentation

Index of the deep‑dive guides for the package. Start with the [README](../README.md) for installation, requirements, and a quick‑start tour; come here for everything else.

## Guides

- **[Advanced Configuration](advanced-configuration.md)** — the full `config/page-builder.php` schema, page & block registration, layouts, middleware, publishing views & translations, manual installation.
- **[Custom Block Development](custom-block-development.md)** — the `Block` base class, all 13 property types with constructor signatures, property groups, shared responsive properties, color handling, a complete example.
- **[Multilingual Support](multilingual-support.md)** — UI locales vs. content locales, multilingual properties, the `LocalizationService` API, runtime locale management, RTL.
- **[Variables](variables.md)** — `{name}` substitution syntax, registering static and dynamic variables, built‑in variables, the parser API.
- **[Theme Service Usage](theme-service-usage.md)** — programmatic export / import / clone / page‑replace via `ThemeService`.
- **[Theme Encryption](theme-encryption.md)** — AES‑256‑GCM encrypted theme exports, key management, runtime overrides.
- **[Performance](performance-optimization.md)** — the dual‑pipeline architecture, where to add caching, Tailwind safe classes, custom‑block performance tips.

## Conventions used in these docs

- File paths are written relative to the package root unless noted otherwise.
- Class names are always shown with their full namespace the first time they appear in a doc, then abbreviated.
- Constructor signatures are reproduced verbatim from the source. If they ever drift, the source is the ground truth — please open an issue.
