<?php

namespace App\Console\Commands;

use App\Jobs\ProcessApartment;
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
            Apartment::query()->truncate();
            Company::query()->chunk(100, function($companies){
                foreach ($companies as $company) {
                    ProcessApartment::dispatch($company->company_id);
                }
            });

        } catch (\Exception $e) {
            logger()->error($e->getMessage());
        }
    }
}
