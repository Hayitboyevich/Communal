<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActionHistory extends Model
{
    use softDeletes;
    protected $guarded = [];

    const TYPE_CREATE = 1;
    const TYPE_UPDATE = 2;
    const TYPE_DELETE = 3;
}
