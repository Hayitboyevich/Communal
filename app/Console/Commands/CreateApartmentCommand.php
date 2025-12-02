<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;

class CreateApartmentCommand extends Command
{
    protected $signature = 'create:apartment';

    protected $description = 'Fetch apartments data for companies and save to DB';

    public function handle()
    {
        try {
//            Apartment::truncate();

            $companies =  Company::query()->whereNotIn('company_id', function($query) {
                $query->select('company_id')->from('apartments');
            })->pluck('id')->toArray();

//            dd($companies);

            Company::query()->whereNotIn('company_id', function($query) {
                $query->select('company_id')->from('apartments');
            })->chunk(100, function ($companies) {
                foreach ($companies as $company) {
                    try {
                        $data = getData(
                            config('apartment.home.url'),
                            config('apartment.home.login'),
                            config('apartment.home.password'),
                            $company->company_id
                        );

                        if (!isset($data['data']) || !is_array($data['data']) || empty($data['data'])) {
                            logger()->error("API bo'sh qaytardi", ['company_id' => $company->company_id, 'response' => $data]);
                            continue;
                        }

                        foreach ($data['data'] as $item) {
                            Apartment::updateOrCreate(
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

                        sleep(1);

                    } catch (\Exception $ex) {
                        logger()->error('Company ID: ' . $company->company_id . ' da xatolik: ' . $ex->getMessage());
                        continue;
                    }
                }
            });

            $this->info('Apartment ma\'lumotlari muvaffaqiyatli yangilandi.');

        } catch (\Exception $e) {
            logger()->error('Umumiy xatolik: ' . $e->getMessage());
            $this->error('Jarayonda xatolik yuz berdi: ' . $e->getMessage());
        }
    }
}


//  php -d max_execution_time=300 artisan create:apartment
