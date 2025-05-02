<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Apartment\Models\MonitoringBase;

class MonitoringBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MonitoringBase::query()->truncate();
        MonitoringBase::create([
            'name' => 'Murojaat asosida'
        ]);

        MonitoringBase::create([
            'name' => 'OAV dan'
        ]);

        MonitoringBase::create([
            'name' => 'Buyruq asosida'
        ]);

        MonitoringBase::create([
            'name' => 'Boshqa xolat'
        ]);


    }
}
