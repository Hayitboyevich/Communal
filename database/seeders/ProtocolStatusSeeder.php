<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Water\Models\ProtocolStatus;

class ProtocolStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProtocolStatus::create([
            'name' => 'Natija kiritishda',
            'description' => 'Natija kiritishda',
        ]);

        ProtocolStatus::create([
            'name' => 'Kamchilik aniqlanmadi',
            'description' => 'Kamchilik aniqlanmadi',
        ]);

        ProtocolStatus::create([
            'name' => 'Ko\'rsatma shakllantirish',
            'description' => 'Ko\'rsatma shakllantirish',
        ]);

        ProtocolStatus::create([
            'name' => 'Ko\'rsatma shakllantirildi',
            'description' => 'Ko\'rsatma shakllantirildi',
        ]);

        ProtocolStatus::create([
            'name' => 'Ma\'muriy qilindi',
            'description' => 'Ma\'muriy qilindi',
        ]);

        ProtocolStatus::create([
            'name' => 'Ko\'rsatma  bajarildi',
            'description' => 'Ko\'rsatma bajarildi',
        ]);

        ProtocolStatus::create([
            'name' => 'HMQOga yuborildi',
            'description' => 'HMQOga yuborildi',
        ]);

        ProtocolStatus::create([
            'name' => 'Korsatmani tasdiqlash',
            'description' => 'Korsatmani tasdiqlash',
        ]);

        ProtocolStatus::create([
            'name' => 'Kamchilik tasdiqlash',
            'description' => 'Kamchilik aniqlanmadini tasdiqlash',
        ]);

        ProtocolStatus::create([
            'name' => 'Yangi',
            'description' => 'yangi',
        ]);


        ProtocolStatus::create([
            'name' => 'Rad qilingan',
            'description' => 'Rad qilingan',
        ]);
    }
}
