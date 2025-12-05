<?php

namespace Modules\Apartment\Models;

use App\Models\Document;
use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProgramRegulation extends Model
{
    protected $guarded = false;

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function objectChecklist(): BelongsTo
    {
        return $this->belongsTo(ProgramObjectChecklist::class, 'program_object_checklist_id');
    }
}
