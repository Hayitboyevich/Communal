<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    protected $guarded = false;
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
