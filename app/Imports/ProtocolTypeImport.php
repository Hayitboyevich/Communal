<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Water\Models\ProtocolType;

class ProtocolTypeImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return new ProtocolType([
            'name' => $row['name'],
        ]);
    }
}
