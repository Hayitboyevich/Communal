<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Region;
use Illuminate\Console\Command;
use Modules\Apartment\Models\Company;

class CreateCompanyCommand extends Command
{
    protected $signature = 'company:create';


    protected $description = 'Command description';


    public function handle()
    {
        try {
            Company::query()->truncate();
            $data = getData(config('apartment.company.company_url'), config('apartment.company.login'), config('apartment.company.password'));

            foreach ($data['data'] as $item) {

                $region = Region::query()->where('soato', $item['country_soato'])->first();
                $district = District::query()->where('soato', $item['region_soato'])->first();
                Company::query()->create([
                    'region_id' => $region->id ?? null,
                    'district_id' => $district->id ?? null,
                    'company_id' => $item['company_id'],
                    'country_soato' => $item['country_soato'],
                    'region_soato' => $item['region_soato'],
                    'company_name' => $item['company_name'],
                    'company_adress' => $item['company_adress'],
                    'company_director' => $item['company_director'],
                    'company_phone' => $item['company_phone'],
                    'company_tin' => $item['company_tin'],
                    'company_account' => $item['company_account'],
                    'company_mfo' => $item['company_mfo'],
                    'company_bank' => $item['company_bank'],
                ]);
            }
        }catch (\Exception $exception){
            echo $exception->getMessage();
        }
    }
}
