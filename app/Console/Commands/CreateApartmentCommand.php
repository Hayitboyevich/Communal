<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;

class CreateApartmentCommand extends Command
{

    protected $signature = 'create:apartment';


    protected $description = 'Command description';


    public function handle()
    {
        try {
            $companies = Company::all();
            Apartment::query()->truncate();
            foreach ($companies as $company) {
                $data = getData(config('apartment.home.url'), config('apartment.home.login'), config('apartment.home.password'), $company->company_id);

                foreach ($data['data'] as $item) {
                    Apartment::query()->create([
                        'company_id' => $company->company_id,
                        'street_name' => $item['street_name'],
                        'street_id' => $item['street_id'],
                        'home_id' => $item['home_id'],
                        'home_name' => $item['home_name'],
                    ]);
                }
            }

        }catch (\Exception $exception){
            echo $exception->getMessage();
        }
    }
}
