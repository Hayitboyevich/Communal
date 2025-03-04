<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regionsPath = storage_path() . "/json/regions.json";

        $regPath = storage_path() . "/json/reg.json";
        $districtsPath = storage_path() . "/json/districts.json";


        $regions = json_decode(file_get_contents($regionsPath), true);
        $districts = json_decode(file_get_contents($districtsPath), true);

        foreach ($regions as &$region) {
            $region['districts'] = [];
            foreach ($districts as $district) {
                if ($district['region_id'] == $region['id']) {
                    $region['districts'][] = $district;
                }
            }
        }

        if (empty(json_decode(file_get_contents($regPath), true))) {
            file_put_contents($regPath, json_encode($regions));
        }

        $meta = json_decode(file_get_contents($regPath), true);


        foreach ($meta as $data) {
            $region = new Region();
            $region->soato = $data['soato'];
            $region->name_uz = $data['uz_name'];
            $region->name_ru = $data['ru_name'];
            $region->region_code = $data['region_code'];
            $region->save();

            foreach ($data['districts'] as $item) {
                $district = new District();
                if (isset($item['ru_name'])) {
                    $district->name_ru = $item['ru_name'];
                }

                if (isset($item['uz_name'])) {
                    $district->name_uz = $item['uz_name'];
                }
                if (isset($item['status'])) {
                    $district->status = $item['status'];
                }
                if (isset($item['soato'])) {
                    $district->soato = $item['soato'];
                }
                $district->region_id = $region->id;
                $district->save();
            }
        }
    }
}
