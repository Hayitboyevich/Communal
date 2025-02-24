<?php

namespace Modules\Water\Models;

use App\Models\District;
use App\Models\Document;
use App\Models\Image;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Water\Enums\ProtocolStatusEnum;

class Protocol extends Model
{
    protected $guarded = false;

    protected $casts = [
        'protocol_status_id' => ProtocolStatusEnum::class,
    ];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProtocolStatus::class, 'protocol_status_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProtocolType::class, 'protocol_type_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
