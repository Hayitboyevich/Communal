<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Water\Models\Defect;

class DefectImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return new Defect([
            'protocol_type_id' => $row['parentid'],
            'name' => $row['name'],
        ]);
    }
}
