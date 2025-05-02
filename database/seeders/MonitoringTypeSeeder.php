<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Apartment\Models\MonitoringType;

class MonitoringTypeSeeder extends Seeder
{

    public function run(): void
    {
        MonitoringType::query()->truncate();

        MonitoringType::create([
            'name' => 'Yerto\'la nazorati'
        ]);

        MonitoringType::create([
            'name' => 'Ko\'p qavatli turar joylarni nazorati'
        ]);

        MonitoringType::create([
            'name' => 'Boshqaruv organlarini faoliyatini o’rganish'
        ]);

        MonitoringType::create([
            'name' => 'Isitish tizimi nazorati'
        ]);

        MonitoringType::create([
            'name' => 'Lift nazorati'
        ]);
    }
}
