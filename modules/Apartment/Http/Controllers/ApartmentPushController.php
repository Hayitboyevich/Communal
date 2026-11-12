<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Apartment\Http\Requests\ApartmentPushRequest;
use Modules\Apartment\Services\ApartmentPushService;

class ApartmentPushController extends BaseController
{
    public function __construct(private readonly ApartmentPushService $service)
    {
        parent::__construct();
    }

    public function push(ApartmentPushRequest $request): JsonResponse
    {
        try {
            $result = $this->service->push($request->validated('apartments'));

            return $this->sendSuccess($result, 'Apartments saved successfully.');
        } catch (\Throwable $e) {
            Log::error('Apartment push error: ' . $e->getMessage(), ['exception' => $e]);

            return $this->sendError(ErrorMessage::ERROR_1, $e->getMessage(), 500);
        }
    }
}
