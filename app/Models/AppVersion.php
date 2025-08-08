<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
   protected $guarded = false;

    protected $hidden = ['created_at', 'updated_at'];
}
