<?php

namespace App\Imports;

use App\Models\ViolationType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
