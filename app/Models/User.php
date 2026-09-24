<?php

namespace App\Models;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Apartment\Models\UserActionHistory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getRoleFromToken()
    {
        return  JWTAuth::parseToken()->getClaim('role_id');
    }
    protected $guarded = false;


    protected $hidden = [
        'password',
    ];


    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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
        return $this->belongsTo(UserStatus::class, 'user_status_id');
    }

    public function fullName(): Attribute
    {
        return Attribute::get(fn () => "{$this->surname} {$this->name} {$this->middle_name}");
    }

    public function scopeSearchByFullName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);
        return $query->where(function ($query) use ($searchTerm) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(middle_name) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(surname) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(phone) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(pin) LIKE ?', ['%' . $searchTerm . '%']);
        });
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function inspectors(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->where('roles.id', UserRoleEnum::INSPECTOR->value);
    }

    public function actionHistories()
    {
        return $this->hasMany(UserActionHistory::class, 'guid')->orderBy('created_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function organizations(): BelongsToMany
    {
        // belongsToMany pivot'dagi soft delete'ni o'zi hisobga olmaydi
        return $this->belongsToMany(Organization::class, 'user_organizations')
            ->using(UserOrganization::class)
            ->withPivot(['id', 'position', 'begin_date', 'dismissed_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function workPlaceSnapshots(): HasMany
    {
        return $this->hasMany(UserWorkPlaceSnapshot::class);
    }

    public function latestWorkPlace(): HasOne
    {
        return $this->hasOne(UserWorkPlaceSnapshot::class)->latestOfMany();
    }
}
