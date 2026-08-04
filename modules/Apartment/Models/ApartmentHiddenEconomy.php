<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartmentHiddenEconomy extends Model
{
    use softDeletes;
    protected $guarded = [];
}
