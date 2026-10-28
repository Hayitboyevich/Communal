<?php

namespace Modules\Apartment\Models;

use App\Enums\LetterType;
use App\Models\District;
use App\Models\Region;
use App\Models\Scopes\LetterTypeScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Water\Models\Protocol;

#[ScopedBy([LetterTypeScope::class])]
class Letter extends Model
{
    protected $guarded = false;

    protected $casts = ['letter_type' => LetterType::class];


    protected static function booted(): void
    {
        static::creating(function (Letter $letter) {
            $letter->letter_type ??= LetterType::current()
                ?? throw new \LogicException('Letter type aniqlanmadi');
        });
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class, 'regulation_id');
    }

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(Monitoring::class, 'monitoring_id');
    }

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class, 'protocol_id');
    }
}
