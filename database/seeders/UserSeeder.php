<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
           'name' => 'Shahzod',
           'middle_name' => 'Hayitboyevich',
           'surname' => 'Ruziev',
           'login' => '123',
           'password' => Hash::make(12345678),
           'region_id' => 1,
           'district_id' => 1,
           'pin' => '31703975270028',
           'phone' => '998337071727',
        ]);
    }
}
