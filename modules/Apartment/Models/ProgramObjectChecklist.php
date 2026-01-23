<?php

namespace Modules\Apartment\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramObjectChecklist extends Model
{
    protected $guarded = false;

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function regulation(): BelongsTo
    {
        return $this->belongsTo(ProgramRegulation::class, 'program_regulation_id');
    }
}
