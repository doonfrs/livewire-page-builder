<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $table = 'builder_themes';

    protected $fillable = ['name', 'description'];

    public function pages(): HasMany
    {
        return $this->hasMany(BuilderPage::class, 'theme_id');
    }
}
