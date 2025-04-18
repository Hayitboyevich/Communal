<?php

namespace Database\Seeders;

use App\Models\UserStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserStatus::create([
            'name' => 'Ishlamoqda',
            'description' => 'Faol',
        ]);

        UserStatus::create([
            'name' => 'Ta\'tilda',
            'description' => 'Ta\'tilda',
        ]);

        UserStatus::create([
            'name' => 'Bo\'shatilgan',
            'description' => 'Bo\'shatilgan',
        ]);
    }
}
