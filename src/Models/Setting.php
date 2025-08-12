<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'builder_settings';

    protected $fillable = ['default_theme_id'];

    protected $casts = [
        'default_theme_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get default theme ID (single row settings)
     */
    public static function getDefaultThemeId(): ?int
    {
        $row = static::query()->find(1);

        return $row?->default_theme_id;
    }

    /**
     * Set default theme ID (single row settings)
     */
    public static function setDefaultThemeId(?int $themeId): void
    {
        static::query()->updateOrCreate(['id' => 1], ['default_theme_id' => $themeId]);
    }
}
