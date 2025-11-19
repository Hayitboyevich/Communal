<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCompany;
use App\Models\District;
use App\Models\Region;
use Illuminate\Console\Command;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;

class CreateCompanyCommand extends Command
{
    protected $signature = 'company:create';


    protected $description = 'Command description';


    public function handle()
    {
        try {
            Company::query()->truncate();
            Apartment::query()->truncate();
            $data = getData(
                config('apartment.company.url'),
                config('apartment.company.login'),
                config('apartment.company.password')
            );

            $total = count($data['data'] ?? []);

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($data['data'] as $item) {

                $region = Region::query()
                    ->where('soato', $item['country_soato'])
                    ->first();

                $district = District::query()
                    ->where('soato', $item['region_soato'])
                    ->first();

                $company = Company::query()->create([
                    'region_id'        => $region->id ?? null,
                    'district_id'      => $district->id ?? null,
                    'company_id'       => $item['company_id'] ?? null,
                    'country_soato'    => $item['country_soato'] ?? null,
                    'region_soato'     => $item['region_soato'] ?? null,
                    'company_name'     => $item['company_name'] ?? null,
                    'company_adress'   => $item['company_adress'] ?? null,
                    'company_director' => $item['company_director'] ?? null,
                    'company_phone'    => $item['company_phone'] ?? null,
                    'company_tin'      => $item['company_tin'] ?? null,
                    'company_account'  => $item['company_account'] ?? null,
                    'company_mfo'      => $item['company_mfo'] ?? null,
                    'company_bank'     => $item['company_bank'] ?? null,
                ]);

                $data = getData(
                    config('apartment.home.url'),
                    config('apartment.home.login'),
                    config('apartment.home.password'),
                    $company->company_id
                );

                if (!isset($data['data']) || !is_array($data['data'])) {
                    logger()->error("API bo'sh qaytardi", [
                        'company_id' => $company->company_id
                    ]);

                    $bar->advance();
                    continue;
                }

                foreach ($data['data'] as $datum) {

                    Apartment::query()->updateOrCreate(
                        [
                            'company_id' => $company->company_id,
                            'street_id'  => $datum['street_id'] ?? null,
                            'home_id'    => $datum['home_id'] ?? null,
                            'home_name'  => $datum['home_name'] ?? null,
                        ],
                        [
                            'street_name' => $datum['street_name'] ?? null,
                        ]
                    );
                }

                sleep(1);

                $bar->advance();
            }

            $bar->finish();

            $this->newLine(2);
            $this->info("Tugadi");

        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }




}
