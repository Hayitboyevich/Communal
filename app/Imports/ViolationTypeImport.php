<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Apartment\Models\ViolationType;

class ViolationTypeImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return new ViolationType([
            'place_id' => $row['parentid'],
            'name' => $row['name'],
        ]);
    }
}
