<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Enums\UserRoleEnum;
use App\Exports\ApartmentHiddenEconomyExport;
use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Apartment\Http\Requests\ApartmentHiddenEconomyExportRequest;
use Modules\Apartment\Http\Requests\CreateHiddenEconomyRequest;
use Modules\Apartment\Models\ApartmentHiddenEconomy;
use Modules\Apartment\Services\TurarJoySyncService;

class ApartmentHiddenEconomyController extends BaseController
{
    public function __construct(
        protected TurarJoySyncService  $turarJoySyncService
    )
    {
        parent::__construct();
    }

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

    public function export(ApartmentHiddenEconomyExportRequest $request)
    {
        try {
            if (!$this->roleId == UserRoleEnum::APARTMENT_MANAGER->value) {
                return $this->sendError(ErrorMessage::ERROR_1, 'Forbidden', 403);
            }

            $validated = $request->validated();

            return Excel::download(
                new ApartmentHiddenEconomyExport(1, $validated),
                'apartment-hidden-economy.xlsx'
            );
        } catch (\Throwable $e) {
            return $this->sendError(ErrorMessage::ERROR_1, $e->getMessage());
        }
    }

    public function checkBot()
    {
        return $this->turarJoySyncService->sync(30380);
    }

}
