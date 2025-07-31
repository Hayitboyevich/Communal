<?php

namespace Database\Seeders;

use App\Imports\DefectImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Water\Models\Defect;

class DefectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Defect::query()->truncate();
        $types = storage_path() . "/excel/defect.xlsx";
        Excel::import(new DefectImport(), $types);
    }
}
