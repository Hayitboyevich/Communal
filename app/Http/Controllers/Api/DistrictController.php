<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function list()
    {
        $region = Region::query()->find(request('region_id'));
        return District::query()->where('region_id', $region->id)->get();
    }
}
