<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Apartment\Models\MonitoringStatus;

class MonitoringStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MonitoringStatus::create([
            'name' => 'Natija kiritishda',
            'description' => 'Natija kiritishda',
        ]);

        MonitoringStatus::create([
            'name' => 'Tasdiqlashda',
            'description' => 'Tasdiqlashda',
        ]);

        MonitoringStatus::create([
            'name' => 'Qoidabuzarlik aniqlanmadi',
            'description' => 'Qoidabuzarlik aniqlanmadi',
        ]);

        MonitoringStatus::create([
            'name' => 'Qoidabuzarlik aniqlandi',
            'description' => 'Qoidabuzarlik aniqlandi',
        ]);

        MonitoringStatus::create([
            'name' => 'Korsatma shakllantirildi',
            'description' => 'Korsatma shakllantirildi',
        ]);


        MonitoringStatus::create([
            'name' => 'Ma\'muriy qilindi',
            'description' => 'Ma\'muriy qilindi',
        ]);

        MonitoringStatus::create([
            'name' => 'Ko\'rsatma  bajarildi',
            'description' => 'Ko\'rsatma bajarildi',
        ]);

        MonitoringStatus::create([
            'name' => 'HMQOga yuborildi',
            'description' => 'HMQOga yuborildi',
        ]);

        MonitoringStatus::create([
            'name' => 'Korsatmani tasdiqlash',
            'description' => 'Korsatmani tasdiqlash',
        ]);

        MonitoringStatus::create([
            'name' => 'Sudga yuborildi',
            'description' => 'Sudga yuborildi',
        ]);

        MonitoringStatus::create([
            'name' => 'MIBga yuborildi',
            'description' => 'MIBga yuborildi',
        ]);

        MonitoringStatus::create([
            'name' => 'SRYXga yuborildi',
            'description' => 'SRYXga yuborildi',
        ]);

        MonitoringStatus::create([
            'name' => 'Chora ko\'rildi',
            'description' => 'Chora ko\'rildi',
        ]);


    }
}
