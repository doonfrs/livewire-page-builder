<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Theme Model
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BuilderPage> $pages
 */
class Theme extends Model
{
    protected $table = 'builder_themes';

    protected $fillable = ['name', 'description'];

    public function pages(): HasMany
    {
        return $this->hasMany(BuilderPage::class, 'theme_id');
    }
}
