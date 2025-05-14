<?php

namespace Database\Seeders;

use App\Imports\ViolationTypeImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Apartment\Models\ViolationType;

class ViolationTypeSeeder extends Seeder
{

    public function run(): void
    {
        ViolationType::query()->truncate();
        $types = storage_path() . "/excel/violation-type.xlsx";
        Excel::import(new ViolationTypeImport(), $types);
    }
}
