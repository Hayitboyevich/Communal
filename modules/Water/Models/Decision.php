<?php

namespace Modules\Water\Models;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Decision extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = false;

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }

}
