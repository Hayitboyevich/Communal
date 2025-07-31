<?php

namespace Modules\Water\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProtocolType extends Model
{
    protected $guarded = false;

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class);
    }
}
