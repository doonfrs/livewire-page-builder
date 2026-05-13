# Variables

The package can substitute named placeholders inside text content at render time. A variable is either a plain string or a closure that returns one — handy for things like the current year, the logged‑in user, a product count, or any other dynamic value editors shouldn't have to type by hand.

---

## Syntax

Use **single curly braces** around the variable name:

```text
Welcome to {company_name}!

We currently have {user_count} registered users.

For support, please contact {support_email}.
```

> ⚠️ The syntax is `{name}`, **not** `{{name}}`. Double braces are reserved for Blade and will not be substituted.

If a placeholder doesn't match any registered variable, it's left as‑is in the output (so unknown placeholders never silently disappear).

The variable name must match `[a-zA-Z0-9_]+` — letters, digits, and underscores. Anything else inside the braces is ignored.

---

## Registering variables

There are two ways to register variables, and they have different trade‑offs.

### 1. Static values, in `config/page-builder.php`

Use this for compile‑time constants:

```php
'variables' => [
    'company_name'  => 'Acme Inc',
    'support_email' => 'support@example.com',
],
```

Values here must be **strings or scalars** — closures are not allowed in the config file because Laravel's config cache can't serialize them.

### 2. Dynamic values, via the `PageBuilderVariables` facade

Use this for anything computed at runtime (DB lookups, the current user, env‑derived values, …). Register inside `AppServiceProvider::boot()` or any other service provider:

```php
use Trinavo\LivewirePageBuilder\Facades\PageBuilderVariables;

public function boot(): void
{
    PageBuilderVariables::register('user_count',  fn () => User::count());
    PageBuilderVariables::register('current_user', fn () => auth()->user()?->name ?? 'Guest');

    PageBuilderVariables::registerMany([
        'company_address' => '123 Main St, Anytown',
        'product_count'   => fn () => Product::count(),
    ]);
}
```

Closures are resolved lazily — they only run when the variable is actually used on a rendered page.

---

## Built‑in variables

The package's service provider registers these for you on every request:

| Name | Value |
|---|---|
| `app_name` | `config('app.name')` |
| `app_url` | `config('app.url')` |
| `year` | The current four‑digit year |
| `current_datetime` | `now()->format('Y-m-d H:i:s')` |

---

## The `PageBuilderVariables` facade

Backed by `Trinavo\LivewirePageBuilder\Config\Variables`:

| Method | Returns | Purpose |
|---|---|---|
| `register(string $name, mixed $value)` | `void` | Register one variable; `$value` may be a string, scalar, or callable |
| `registerMany(array $variables)` | `void` | Register many at once (associative array keyed by name) |
| `get(string $name, mixed $default = null)` | `mixed` | Read a variable; resolves closures automatically |
| `all()` | `array<string, mixed>` | All variables with closures already resolved |
| `has(string $name)` | `bool` | Whether a variable is registered |
| `remove(string $name)` | `void` | Unregister a single variable |
| `clear()` | `void` | Unregister everything |

---

## Using the parser directly

If you want to apply substitution outside the standard render pipeline (e.g. inside a custom block's `render()` method), call the parser:

```php
use Trinavo\LivewirePageBuilder\Support\VariablesParser;

$rendered = VariablesParser::parse($this->content);
```

Two more utilities are available on the same class:

```php
VariablesParser::containsVariables($text);     // bool — does the text have any {placeholders}?
VariablesParser::listVariablesInText($text);   // array — every {name} found in the text
```

---

## See also

- [Custom Block Development](custom-block-development.md) — where to apply variables inside block `render()`
- [Multilingual Support](multilingual-support.md) — how variables interact with multilingual content
