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
        ]);

        ProtocolStatus::create([
            'name' => 'Kamchilik aniqlanmadi',
        ]);

        ProtocolStatus::create([
            'name' => 'Ko\'rsatma shakllantirish',
        ]);

        ProtocolStatus::create([
            'name' => 'Ko\'rsatma shakllantirildi',
        ]);

        ProtocolStatus::create([
            'name' => 'Ma\'muriy qilindi',
        ]);

        ProtocolStatus::create([
            'name' => 'Ko\'rsatma bajarildi',
        ]);

        ProtocolStatus::create([
            'name' => 'HMQOga yuborildi',
        ]);
    }
}
