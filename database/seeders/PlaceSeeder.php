<?php

namespace Database\Seeders;

use App\Models\Place;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Place::query()->truncate();

        Place::query()->create([
            'name' => 'Yerto\'lada o\'zboshimchalik bilan o\'zgartirilgan holatlar',
            'monitoring_type_id' => 1,
        ]);

        Place::query()->create([
            'name' => 'Yerto\'ladan foydalanish tartibi buzilgan holatlar',
            'monitoring_type_id' => 1,
        ]);

        Place::query()->create([
            'name' => 'Ko\'p qavatli turarjoylarda o\'zboshimchalik bilan o\'zgartirilgan holatlar',
            'monitoring_type_id' => 2,
        ]);
    }
}
