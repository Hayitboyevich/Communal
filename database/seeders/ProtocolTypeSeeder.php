<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Water\Models\ProtocolType;

class ProtocolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProtocolType::create([
            'name' => 'Bilmadm',
            'description' => 'Bilmadm',
        ]);
    }
}
