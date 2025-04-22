<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BasicUserSeeder extends Seeder
{

    public function run(): void
    {
        User::query()->create([
            'name' => 'suv nazorati',
            'login' => 'suv_nazorat',
            'password' => Hash::make('39gvT6l<kdyT'),
            'type' => 1,
        ]);
    }
}
