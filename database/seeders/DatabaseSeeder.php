<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RegionSeeder::class,
            UserSeeder::class,
            ProtocolStatusSeeder::class,
            ProtocolTypeSeeder::class,
            UserStatusSeeder::class,
        ]);
    }
}
