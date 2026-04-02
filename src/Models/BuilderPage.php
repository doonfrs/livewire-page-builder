<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Trinavo\LivewirePageBuilder\Events\BuilderPageSaved;

/**
 * Class BuilderPage
 *
 * @property int $id
 * @property string $key
 * @property array|null $components
 * @property int|null $theme_id
 * @property bool $is_block
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Theme|null $theme
 */
class BuilderPage extends Model
{
    protected $fillable = ['key', 'components', 'theme_id', 'is_block'];

    protected $dispatchesEvents = [
        'saved' => BuilderPageSaved::class,
    ];

    protected $casts = [
        'components' => 'json',
        'is_block' => 'boolean',
    ];

    /**
     * Get the theme that owns the builder page.
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }
}
