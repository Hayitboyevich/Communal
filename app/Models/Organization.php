<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $guarded = false;

    protected $casts = [
        'notification_send' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        // belongsToMany pivot'dagi soft delete'ni o'zi hisobga olmaydi
        return $this->belongsToMany(User::class, 'user_organizations')
            ->using(UserOrganization::class)
            ->withPivot(['id', 'position', 'begin_date', 'dismissed_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
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
