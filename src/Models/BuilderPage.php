<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuilderPage extends Model
{
    protected $fillable = ['key', 'components', 'theme_id', 'is_block'];

    protected $casts = [
        'components' => 'json',
        'is_block' => 'boolean',
    ];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }
}
