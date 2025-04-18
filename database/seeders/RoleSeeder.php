<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{

    public function run(): void
    {
        Role::query()->create([
            'name' => 'admin',
            'description' => 'admin'
        ]);

        Role::query()->create([
            'name' => 'inspektor',
            'description' => 'inspektor'
        ]);

        Role::query()->create([
            'name' => 'suv inspektor',
            'description' => 'suv inspektor'
        ]);

        Role::query()->create([
            'name' => 'Kadr',
            'description' => 'Res kadr'
        ]);

        Role::query()->create([
            'name' => 'Viloyat inspektor',
            'description' => 'Viloyat inspektor'
        ]);
    }
}
