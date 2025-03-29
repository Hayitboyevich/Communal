<?php

namespace Modules\Water\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
    protected $guarded = false;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($card) {
            $card->status = !Card::query()->where('user_id', $card->user_id)->exists();
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
