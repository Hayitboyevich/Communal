<?php

namespace Modules\Apartment\Models;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = false;

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
