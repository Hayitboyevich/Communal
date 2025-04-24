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
            'name' => 'Inspektor Kadr',
            'description' => 'Res kadr',
            'child' => [2, 5]
        ]);

        Role::query()->create([
            'name' => 'Boshliq o\'rinbosari',
            'description' => 'Viloyat inspektor'
        ]);

        Role::query()->create([
            'name' => 'Suv Kadr',
            'description' => 'Suv kadr',
            'child' => [3]
        ]);
    }
}
