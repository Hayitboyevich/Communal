<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MonitoringCreateSecondRequest;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Http\Requests\MonitoringChangeStatusRequest;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Http\Requests\ViolationRequest;
use Modules\Apartment\Http\Resources\MonitoringResource;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Services\MonitoringService;
use Illuminate\Http\Request;

class MonitoringController extends BaseController
{
    public function __construct(
        protected MonitoringService $service
    )
    {
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['status', 'type']);
            $monitorings = $id
                ? $this->service->findById($id)
                : $this->service->getAll($this->user, $this->roleId, $filters)->paginate(request('per_page', 15));

            $resource = $id
                ? MonitoringResource::make($monitorings)
                : MonitoringResource::collection($monitorings);

            return $this->sendSuccess(
                $resource,
                $id ? 'Protocol retrieved successfully.' : 'Protocols retrieved successfully.',
                $id ? null : pagination($monitorings)
            );

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function count(): JsonResponse
    {
        try {
            $filters = request()->only(['status', 'type']);
            $data = $this->service->count($this->user, $this->roleId, $filters);
            return $this->sendSuccess($data, 'Count');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function report($regionId = null): JsonResponse
    {
        try {
            $startDate = request('date_from');
            $endDate = request('date_to');

            $regionId = request('region_id');

            $regions = $regionId
                ? District::query()->where('region_id', $regionId)->get(['id', 'name_uz'])
                : Region::all(['id', 'name_uz']);

            $group = $regionId ? 'district_id' : 'region_id';

            $userCounts = User::query()
                ->selectRaw($group . ', COUNT(*) as count')
                ->leftJoin('user_roles', 'user_roles.user_id', '=', 'users.id')
                ->where('user_roles.role_id', UserRoleEnum::APARTMENT_INSPECTOR->value)
                ->groupBy($group)
                ->pluck('count', $group);

            $protocolCounts = $this->getGroupedCounts(
                query: Monitoring::query(),
                selectRaw: $group,
                groupBy: [$group, 'monitoring_status_id'],
                startDate: $startDate,
                endDate: $endDate
            )
                ->groupBy($group);



            $data = $regions->map(function ($region) use ($userCounts, $protocolCounts) {
                $regionId = $region->id;
                $regionProtocols = collect($protocolCounts->get($regionId, []));

                $sumByStatus = fn($statuses) =>
                $regionProtocols->whereIn('monitoring_status_id', (array)$statuses)->sum('count');

                return [
                    'id' => $region->id,
                    'name' => $region->name_uz,
                    'inspector_count' => $userCounts->get($regionId, 0),
                    'all_monitorings' => $regionProtocols->sum('count'),
                    'all_defect_count' => $sumByStatus([
                        MonitoringStatusEnum::DEFECT->value,
                        MonitoringStatusEnum::DONE->value,
                        MonitoringStatusEnum::COURT->value,
                        MonitoringStatusEnum::MIB->value,
                        MonitoringStatusEnum::FIXED->value,
                        MonitoringStatusEnum::ADMINISTRATIVE->value,
                        MonitoringStatusEnum::CONFIRM_RESULT->value,
                    ]),
                    'all_fix' => $sumByStatus([
                        MonitoringStatusEnum::DONE->value,
                        MonitoringStatusEnum::ADMINISTRATIVE->value,
                        MonitoringStatusEnum::COURT->value,
                        MonitoringStatusEnum::MIB->value,
                        MonitoringStatusEnum::HMQO->value,
                        MonitoringStatusEnum::FIXED->value,
                    ]),
                    'fix_done' => $sumByStatus(MonitoringStatusEnum::DONE->value),
                    'fix_administrative' => $sumByStatus(MonitoringStatusEnum::ADMINISTRATIVE->value),
                    'fix_court' => $sumByStatus(MonitoringStatusEnum::COURT->value),
                    'fix_mib' => $sumByStatus(MonitoringStatusEnum::MIB->value),
                    'fix_hmqo' => $sumByStatus(MonitoringStatusEnum::HMQO->value),
                    'fixed' => $sumByStatus(MonitoringStatusEnum::FIXED->value),
                ];
            });

            return $this->sendSuccess($data->values(), 'Data retrieved successfully');

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


    public function history($id): JsonResponse
    {
        try {
            $data = $this->service->history($id);
            return $this->sendSuccess($data, 'History');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(MonitoringCreateRequest $request): JsonResponse
    {
        try {
            $monitoring = $this->service->create($request);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring created successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createSecond($id, MonitoringCreateSecondRequest $request): JsonResponse
    {
        try {
            $this->service->createSecond($id, $request);
            return $this->sendSuccess([], 'Monitoring created successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createThird($id, ViolationRequest $request): JsonResponse
    {
        try {
            $this->service->createThird($id, $request);
            return $this->sendSuccess([], 'Monitoring violation successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function confirm($id): JsonResponse
    {
        try {
            $monitoring = $this->service->confirm($id);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function reject($id, Request $request): JsonResponse
    {
        try {
            $monitoring = $this->service->reject($id, $request);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function confirmRegulation($id): JsonResponse
    {
        try {
            $monitoring = $this->service->confirmRegulation($id);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function attach(): JsonResponse
    {
        try {
            $monitoring = $this->service->attach(\request('user_id'), \request('monitoring_id'));
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring attached successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function pdf():JsonResponse
    {
        try {

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function rejectRegulation($id, Request $request): JsonResponse
    {
        try {
            $monitoring = $this->service->rejectRegulation($id, $request);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function changeStatus($id, MonitoringChangeStatusRequest $request): JsonResponse
    {
        try {
            $monitoring = $this->service->changeStatus($id, $request);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    private function getGroupedCounts($query, $selectRaw, $groupBy, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query
            ->selectRaw("$selectRaw, monitoring_status_id,  COUNT(*) as count")
            ->groupBy(...$groupBy)
            ->get();
    }


}
