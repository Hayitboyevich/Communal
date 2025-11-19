<?php

namespace App\Jobs;

use App\Models\District;
use App\Models\Region;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Apartment\Models\Company;

class ProcessCompany implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $timeout = 60;
    public $tries = 3;

    public function handle()
    {
        $data = getData(config('apartment.company.url'), config('apartment.company.login'), config('apartment.company.password'));

        foreach ($data as $item) {
            $region = Region::query()->where('soato', $item['country_soato'])->first();
                $district = District::query()->where('soato', $item['region_soato'])->first();
                $company = Company::query()->updateOrCreate(
                ['company_id' => $item['company_id']],
                [
                    'region_id' => $region->id ?? null,
                    'district_id' => $district->id ?? null,
                    'country_soato' => $item['country_soato'] ?? null,
                    'region_soato' => $item['region_soato'] ?? null,
                    'company_name' => $item['company_name'] ?? null,
                    'company_adress' => $item['company_adress'] ?? null,
                    'company_director' => $item['company_director'] ?? null,
                    'company_phone' => $item['company_phone'] ?? null,
                    'company_tin' => $item['company_tin']  ?? null,
                    'company_account' => $item['company_account'] ?? null,
                    'company_mfo' => $item['company_mfo']   ?? null,
                    'company_bank' => $item['company_bank']   ?? null,
                ]
            );
            logger()->error("yaratildi", ['company' => $company]);
            ProcessApartment::dispatch($company->id);
        }
    }
}
