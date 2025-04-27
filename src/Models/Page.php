<?php

namespace Trinavo\LivewirePageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'builder_pages';

    protected $fillable = ['key', 'components'];

    protected $casts = [
        'components' => 'array',
    ];
}
