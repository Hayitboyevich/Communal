<?php

namespace Modules\Apartment\Models;


use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramObject extends Model
{
    protected $guarded = false;

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(
            ProgramObjectChecklist::class,
            'program_object_checklists',
            'program_object_id',
            'checklist_id'
        )->withPivot(['plan', 'unit', 'program_id'])
            ->withTimestamps();
    }

}
