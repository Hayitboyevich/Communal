<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Video extends Model
{
    protected $guarded = false;

    public function videoable(): MorphTo
    {
        return $this->morphTo();
    }
}
