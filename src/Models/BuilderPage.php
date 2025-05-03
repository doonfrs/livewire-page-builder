<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class BuilderPage extends Model
{
    protected $fillable = ['key', 'components', 'theme'];

    protected $casts = [
        'components' => 'array',
    ];
}
