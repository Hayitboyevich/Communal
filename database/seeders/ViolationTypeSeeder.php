<?php

namespace Database\Seeders;

use App\Imports\ViolationTypeImport;
use App\Models\ViolationType;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class ViolationTypeSeeder extends Seeder
{

    public function run(): void
    {
        ViolationType::query()->truncate();
        $types = storage_path() . "/excel/violation-type.xlsx";
        Excel::import(new ViolationTypeImport(), $types);
    }
}
