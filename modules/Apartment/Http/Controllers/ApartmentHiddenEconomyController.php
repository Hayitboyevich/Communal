<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Http\Requests\CreateHiddenEconomyRequest;
use Modules\Apartment\Models\ApartmentHiddenEconomy;

class ApartmentHiddenEconomyController extends BaseController
{

    public function attachInspector (CreateHiddenEconomyRequest $request)
    {
        $validated = $request->validated();
        try {
            $apartment_hidden_economy = DB::transaction(function () use ($validated) {
                return ApartmentHiddenEconomy::query()->create($validated);
            });
            return $this->sendSuccess($apartment_hidden_economy, 'Inspector attached successfully.');
        } catch (Exception $e){
            return $this->sendError(ErrorMessage::ERROR_1, $e->getMessage());
        } catch (\Throwable $e) {
            return $this->sendError(ErrorMessage::ERROR_1, $e->getMessage());
        }
    }

}
