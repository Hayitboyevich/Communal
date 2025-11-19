<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;

class ProcessApartment implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $companyId){}

    public function handle()
    {
        $company = Company::find($this->companyId);
        if (!$company) return;

        $data = getData(
            config('apartment.home.url'),
            config('apartment.home.login'),
            config('apartment.home.password'),
            $company->company_id
        );

        if (!isset($data['data']) || !is_array($data['data'])) {
            logger()->error("API bo'sh qaytardi", ['company_id' => $this->companyId]);
            return;
        }

        foreach ($data['data'] as $item) {

            Apartment::query()->updateOrCreate(
                [
                    'company_id' => $company->company_id,
                    'street_id'  => $item['street_id'] ?? null,
                    'home_id'    => $item['home_id'] ?? null,
                    'home_name'  => $item['home_name'] ?? null,
                ],
                [
                    'street_name' => $item['street_name'] ?? null,
                ]
            );
        }

    }
}
