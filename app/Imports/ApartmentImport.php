<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Region;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Apartment\Models\Apartment;

class ApartmentImport  implements WithHeadingRow, ToModel
{
    public function model(array $row)
    {
        return new Apartment([
            'company_id' => $row['company_id'],
            'street_name' => $row['street_name'],
            'street_id' => $row['street_id'],
            'home_id' => $row['home_id'],
            'home_name' => $row['home_name'],

        ]);
    }
}
