<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    protected $guarded = false;

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
