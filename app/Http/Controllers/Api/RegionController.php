<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index($id = null)
    {
       if ($id) {
           return Region::query()->find($id);
       }else{
           return Region::all();
       }
    }
}
