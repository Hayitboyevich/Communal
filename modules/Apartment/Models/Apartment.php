<?php

namespace Modules\Apartment\Models;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $guarded = false;


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function monitorings()
    {
        return $this->hasMany(Monitoring::class, 'apartment_id', 'home_id');
    }

    public function apartmentHiddenEconomy()
    {
        return $this->hasMany(ApartmentHiddenEconomy::class, 'home_id', 'home_id');
    }
}
