<?php

namespace Modules\Apartment\Models;

use App\Models\Document;
use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActionHistory extends Model
{
    use softDeletes;
    protected $guarded = [];

    protected $casts = [
        'content' => 'object',
    ];

    const TYPE_CREATE = 1;
    const TYPE_UPDATE = 2;
    const TYPE_DELETE = 3;

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
