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
            'name' => 'Mutaxassis',
            'description' => 'inspektor'
        ]);

        Role::query()->create([
            'name' => 'Suv mutaxassisi',
            'description' => 'suv inspektor'
        ]);

        Role::query()->create([
            'name' => 'Kadr',
            'description' => 'Res kadr'
        ]);

        Role::query()->create([
            'name' => 'Boshliq o\'rinbosari',
            'description' => 'Viloyat inspektor'
        ]);

        Role::query()->create([
            'name' => 'Kadr',
            'description' => 'Suv kadr'
        ]);
    }
}
