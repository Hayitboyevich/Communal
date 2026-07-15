<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Region;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Apartment\Models\Company;

class CompanyImport implements WithHeadingRow, ToModel
{
    public function model(array $row)
    {
        $region = Region::where('soato', $row['country_soato'])->first();
        $district = District::where('soato', $row['region_soato'])->first();
        return new Company([
            'company_id' => $row['company_id'],
            'country_soato' => $row['country_soato'],
            'region_soato' => $row['region_soato'],
            'region_id' => $region->id,
            'district_id' => $district->id,
            'company_name' => $row['company_name'],
            'company_adress' => $row['company_adress'],
            'company_director' => $row['company_director'],
            'company_phone' => $row['company_phone'],
            'company_tin' => $row['company_tin'],
            'company_account' => $row['company_account'],
            'company_mfo' => $row['company_mfo'],
            'company_bank' => $row['company_bank'],
        ]);
    }
}
