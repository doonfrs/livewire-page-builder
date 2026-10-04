<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

/**
 * Theme Model
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BuilderPage> $pages
 */
class Theme extends Model
{
    /**
     * The settings key of the picture shown on the theme's card in the Theme
     * Manager. A URL, so it travels with the theme through export, import and
     * clone like every other setting. Built in rather than host-defined: the
     * Theme Manager itself reads it.
     */
    public const PREVIEW_IMAGE_SETTING = 'preview_image';

    protected $table = 'builder_themes';

    protected $fillable = ['name', 'description', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(BuilderPage::class, 'theme_id');
    }

    /**
     * Read a theme setting by dot key (e.g. 'slider_images.desktop.width').
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    /**
     * Set a theme setting by dot key. Does not persist; call save().
     */
    public function setSetting(string $key, mixed $value): static
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;

        return $this;
    }

    /**
     * The URL of the theme's card picture, or null when it has none.
     */
    public function previewImageUrl(): ?string
    {
        $url = $this->getSetting(self::PREVIEW_IMAGE_SETTING);

        return is_string($url) && $url !== '' ? $url : null;
    }

    /**
     * Remove a theme setting by dot key. Does not persist; call save().
     */
    public function forgetSetting(string $key): static
    {
        $settings = $this->settings ?? [];
        Arr::forget($settings, $key);
        $this->settings = $settings ?: null;

        return $this;
    }
}
