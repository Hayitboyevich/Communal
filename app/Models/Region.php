<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $guarded = false;

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
