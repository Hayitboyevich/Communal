<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Exports\ViolationReportExport;
use App\Http\Controllers\BaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Requests\ViolationReportRequest;
use Modules\Apartment\Services\ViolationReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViolationReportController extends BaseController
{
    public function __construct(protected ViolationReportService $service)
    {
        parent::__construct();
    }

    /**
     * GET /violation-report/{type}?region_id=&date_from=&date_to=
     * type: 1-tayanch | 2-tutash | 3-yertola | 4-umumfoydalanish | 5-umumiy
     */
    public function show(ViolationReportRequest $request, int $type): JsonResponse
    {
        try {
            $report = $this->service->build($type, $request->validated(), $this->user, $this->roleId);

            return $this->sendSuccess($report, 'Violation report');
        } catch (AuthorizationException $exception) {
            return $this->sendError($exception->getMessage(), [], 403);
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    /**
     * GET /violation-report/{type}/excel?region_id=&date_from=&date_to=
     */
    public function excel(ViolationReportRequest $request, int $type): StreamedResponse|JsonResponse
    {
        try {
            $report = $this->service->build($type, $request->validated(), $this->user, $this->roleId);

            return (new ViolationReportExport($report))->download();
        } catch (AuthorizationException $exception) {
            return $this->sendError($exception->getMessage(), [], 403);
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
