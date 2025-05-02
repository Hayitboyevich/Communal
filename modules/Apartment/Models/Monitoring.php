<?php

namespace Modules\Apartment\Models;

use App\Models\Document;
use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Water\Enums\ProtocolStatusEnum;

class Monitoring extends Model
{
    protected $guarded = false;

//    protected $casts = [
//        'monit_status_id' => ProtocolStatusEnum::class,
//    ];
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
