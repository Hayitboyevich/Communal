<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Apartment\Models\Checklist;
use Modules\Apartment\Models\WorkType;

class WorkTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workTypes = [
          [
              'id' => 1,
              'name' => "Ko‘p kvartirali uylarni ta'mirlash",
          ],
          [
              'id' => 2,
              'name' => "Tutash hududni obodonlashtirish",
          ]
        ];

        $checkLists = [
          [
              'work_type_id' => 1,
              'name' => "Tomni ta’mirlash",
              'unit' => 'kv.m'
          ],
            [
                'work_type_id' => 1,
                'name' => "Kirish yo‘laklarini ta’mirlash (podyezd)",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 1,
                'name' => "Kirish yo‘laklarining kirish qismidagi koziryoklarni ta’mirlash",
                'unit' => 'kv.m'
            ],

            [
                'work_type_id' => 1,
                'name' => "Kodli qulfli temir eshiklarni o‘rnatish",
                'unit' => 'ta'
            ],

            [
                'work_type_id' => 1,
                'name' => "Framugalarga plastik romlar o‘rnatish",
                'unit' => 'kv.m'
            ],
            [
                'work_type_id' => 1,
                'name' => "Elektr taqsimlovchi shitlarni ta’mirlash",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 1,
                'name' => "Yerto‘la qismidagi muhandislik kommunikatsiyalarini ta’mirlash",
                'unit' => 'p/m'
            ],
            [
                'work_type_id' => 1,
                'name' => "Uy ichidagi muhandislik tarmoqlarini ta’mirlash",
                'unit' => 'p/m'
            ],
            [
                'work_type_id' => 1,
                'name' => "Fasadni ta’mirlash",
                'unit' => 'kv/m'
            ],
            [
                'work_type_id' => 1,
                'name' => "Tarnovlarni ta’mirlash (livnyovka)",
                'unit' => 'p/m'
            ],

            [
                'work_type_id' => 1,
                'name' => "Uylarning yon tomon devorlari ulangan qismini (torseviye shvi) ta’mirlash",
                'unit' => 'p/m'
            ],

            [
                'work_type_id' => 2,
                'name' => "Bolalar maydonchalarini qurish ",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 2,
                'name' => "Bolalar maydonchalarini ta’mirlash ",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 2,
                'name' => "Irrigatsiya tarmoqlarini ta’mirlash",
                'unit' => 'p/m'
            ],
            [
                'work_type_id' => 2,
                'name' => "Yo‘laklarni betonlash",
                'unit' => 'kv.m'
            ],

            [
                'work_type_id' => 2,
                'name' => "Tashqi yoritish tizimini ta’mirlash va yangilash ",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 2,
                'name' => "Umumfoydalanishdagi hojatxonalarni qurish ",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 2,
                'name' => "Umumfoydalanishdagi tandirxonalarni qurish va ta’mirlash",
                'unit' => 'ta'
            ],
            [
                'work_type_id' => 2,
                'name' => "Uy atrofidagi kichik yo‘laklarni (otmostka) ta’mirlash",
                'unit' => 'p/m'
            ],
        ];

        foreach ($workTypes as $workType) {
            WorkType::query()->create($workType);
        }

        foreach ($checkLists as $checkList) {
            CheckList::query()->create($checkList);
        }
    }
}
