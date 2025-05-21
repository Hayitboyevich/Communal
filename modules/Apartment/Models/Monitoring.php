<?php

namespace Modules\Apartment\Models;

use App\Models\District;
use App\Models\Document;
use App\Models\Image;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function type(): BelongsTo
    {
       return $this->belongsTo(MonitoringType::class, 'monitoring_type_id');
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(MonitoringBase::class, 'monitoring_base_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'home_id');
    }
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(MonitoringStatus::class, 'monitoring_status_id');
    }

    public function regulation(): HasOne
    {
        return $this->hasOne(Regulation::class, 'monitoring_id');
    }

    public function violation(): HasOne
    {
        return $this->hasOne(Violation::class, 'monitoring_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(MonitoringHistory::class, 'guid');
    }
}
