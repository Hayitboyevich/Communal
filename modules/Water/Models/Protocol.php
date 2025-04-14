<?php

namespace Modules\Water\Models;

use App\Models\District;
use App\Models\Document;
use App\Models\Image;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProtocolStatus::class, 'protocol_status_id');
    }

    public function protocolType(): BelongsTo
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
