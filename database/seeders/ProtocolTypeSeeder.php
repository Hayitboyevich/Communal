<?php

namespace Database\Seeders;

use App\Imports\ProtocolTypeImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Water\Models\ProtocolType;

class ProtocolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProtocolType::query()->truncate();
        $types = storage_path() . "/excel/protocol-type.xlsx";
        Excel::import(new ProtocolTypeImport(), $types);
    }
}
