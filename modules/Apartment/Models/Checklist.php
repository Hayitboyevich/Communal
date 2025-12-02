<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checklist extends Model
{
    protected $guarded = false;

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
