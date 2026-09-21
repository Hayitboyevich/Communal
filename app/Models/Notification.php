<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Termwind\renderUsing;

class Notification extends Model
{
    use softDeletes;
    protected $guarded = [];
    protected $casts = [
        'data' => 'object',
        'read_at' => 'datetime',
    ];

    protected $hidden = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
