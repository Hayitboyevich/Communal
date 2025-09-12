<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Constants\FineType;
use App\Enums\UserRoleEnum;
use App\Exports\MonitoringExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MonitoringCreateSecondRequest;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Apartment\Enums\MonitoringStatusEnum;
use Modules\Apartment\Http\Requests\MonitoringChangeStatusRequest;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Http\Requests\ViolationRequest;
use Modules\Apartment\Http\Resources\MonitoringResource;
use Modules\Apartment\Models\Monitoring;
use Modules\Apartment\Services\MonitoringService;
use Illuminate\Http\Request;
use Modules\Water\Const\Step;
use Modules\Water\Http\Resources\FineResource;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            $filters = request()->only(['status', 'type', 'category', 'region_id', 'district_id', 'id', 'monitoring_type']);
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
            $filters = request()->only(['status', 'type', 'category', 'region_id', 'district_id', 'id', 'monitoring_type']);
            $data = $this->service->count($this->user, $this->roleId, $filters);
            return $this->sendSuccess($data, 'Count');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


    private function getMonitoringCounts($query, $group, $startDate = null, $endDate = null)
    {
        $violationsSub = DB::table('violations')
            ->selectRaw("
            monitoring_id,
            MAX(CASE WHEN deadline IS NOT NULL THEN 1 ELSE 0 END) AS has_deadline
        ")
            ->groupBy('monitoring_id');

        if ($startDate && $endDate) {
            $query->whereBetween('monitorings.created_at', [$startDate, $endDate]);
        }

        return $query
            ->leftJoinSub($violationsSub, 'vio', function ($join) {
                $join->on('vio.monitoring_id', '=', 'monitorings.id');
            })
            ->selectRaw("
            $group as group_id,
            COUNT(DISTINCT monitorings.id) AS count,

            COALESCE(SUM(vio.has_deadline), 0) AS fix_formed,
            SUM(CASE WHEN monitorings.step = 4 THEN 1 ELSE 0 END) AS fix_done,
            SUM(CASE WHEN monitorings.is_administrative = TRUE THEN 1 ELSE 0 END) AS fix_administrative,
            SUM(CASE WHEN monitorings.send_court = TRUE THEN 1 ELSE 0 END) AS fix_court,
            SUM(CASE WHEN monitorings.send_mib = TRUE THEN 1 ELSE 0 END) AS fix_mib,
            SUM(CASE WHEN monitorings.send_chora = TRUE THEN 1 ELSE 0 END) AS fixed
        ")
            ->groupBy($group)
            ->get();
    }
    private function getDecisionCounts($query, $group, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween('monitorings.created_at', [$startDate, $endDate]);
        }

        return $query
            ->join('decisions', 'decisions.id', '=', 'monitorings.decision_id')
            ->where('decisions.project_id', FineType::APARTMENT)
            ->selectRaw("
            $group as group_id,
            COUNT(decisions.id) AS decision_count,
            COUNT(decisions.id) FILTER (WHERE decisions.decision_status = 12) AS paid_count,
            COUNT(decisions.id) FILTER (WHERE decisions.decision_status != 12 OR decisions.decision_status IS NULL) AS unpaid_count,

            SUM(DISTINCT CASE WHEN decisions.decision_status IS NOT NULL THEN decisions.main_punishment_amount::numeric ELSE 0 END) AS total_amount,
            SUM(DISTINCT CASE WHEN decisions.decision_status = 12 THEN decisions.main_punishment_amount::numeric ELSE 0 END) AS paid_amount,
            SUM(DISTINCT CASE WHEN decisions.decision_status != 12 OR decisions.decision_status IS NULL THEN decisions.main_punishment_amount::numeric ELSE 0 END) AS unpaid_amount
        ")
            ->groupBy($group)
            ->get();
    }



    public function report($regionId = null): JsonResponse
    {
        try {
            $startDate = request('date_from');
            $endDate   = request('date_to');
            $regionId  = request('region_id');

            $regions = $regionId
                ? District::query()->where('region_id', $regionId)->get(['id', 'name_uz'])
                : Region::all(['id', 'name_uz']);

            $group = $regionId ? 'monitorings.district_id' : 'monitorings.region_id';

            $userCounts = User::query()
                ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
                ->where('user_roles.role_id', UserRoleEnum::APARTMENT_INSPECTOR->value)
                ->selectRaw(($regionId ? 'users.district_id' : 'users.region_id') . ' as group_id, COUNT(users.id) as count')
                ->groupBy('group_id')
                ->pluck('count', 'group_id');

            $monitoringCounts = $this->getMonitoringCounts(
                Monitoring::query(),
                $group,
                $startDate,
                $endDate
            )->groupBy('group_id');

            $decisionCounts = $this->getDecisionCounts(
                Monitoring::query(),
                $group,
                $startDate,
                $endDate
            )->pluck(null, 'group_id'); // key => row

            $data = $regions->map(function ($region) use ($userCounts, $monitoringCounts, $decisionCounts) {
                $regionId = $region->id;
                $regionMonitoring = $monitoringCounts->get($regionId, collect());
                $regionDecision   = $decisionCounts->get($regionId);
                $sumByStatus = fn($statuses) =>
                $regionMonitoring->whereIn('status_id', (array)$statuses)->sum('count');

                return [
                    'id'                  => $region->id,
                    'name'                => $region->name_uz,
                    'inspector_count'     => $userCounts->get($regionId, 0),

                    'all_monitorings'     => $regionMonitoring->sum('count'),
                    'all_defect_count'    => $sumByStatus([
                        MonitoringStatusEnum::DEFECT,
                        MonitoringStatusEnum::DONE,
                        MonitoringStatusEnum::COURT,
                        MonitoringStatusEnum::MIB,
                        MonitoringStatusEnum::FIXED,
                        MonitoringStatusEnum::FORMED,
                        MonitoringStatusEnum::ADMINISTRATIVE,
                        MonitoringStatusEnum::CONFIRM_RESULT,
                    ]),
                    'all_fix'             => $sumByStatus([
                        MonitoringStatusEnum::FORMED,
                        MonitoringStatusEnum::DONE,
                        MonitoringStatusEnum::ADMINISTRATIVE,
                        MonitoringStatusEnum::COURT,
                        MonitoringStatusEnum::MIB,
                        MonitoringStatusEnum::HMQO,
                        MonitoringStatusEnum::FIXED,
                    ]),
                    'fix_formed'          => $regionMonitoring->sum('fix_formed'),
                    'fix_done'            => $regionMonitoring->sum('fix_done'),
                    'fix_administrative'  => $regionMonitoring->sum('fix_administrative'),
                    'fix_court'           => $regionMonitoring->sum('fix_court'),
                    'fix_mib'             => $regionMonitoring->sum('fix_mib'),
                    'fixed'               => $regionMonitoring->sum('fixed'),

                    'decision_count'      => $regionDecision->decision_count ?? 0,
                    'paid_count'          => $regionDecision->paid_count ?? 0,
                    'unpaid_count'        => $regionDecision->unpaid_count ?? 0,
                    'total_amount'        => $regionDecision->total_amount ?? 0,
                    'paid_amount'         => $regionDecision->paid_amount ?? 0,
                    'unpaid_amount'       => $regionDecision->unpaid_amount ?? 0,
                ];
            });

            return $this->sendSuccess($data->values(), 'Data retrieved successfully');

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


    public function excel($id)
    {
        try {
            return Excel::download(new MonitoringExport($id), 'protocol.xlsx');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
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

    public function fine($id): JsonResponse
    {
        try {
            $monitoring = $this->service->findById($id);
            return $this->sendSuccess(FineResource::make($monitoring->fine), 'Success');
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
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function pdf($id): JsonResponse
    {
        try {
            $monitoring = Monitoring::find($id);
            $domain = URL::to('/regulation-info') . '/' . $id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
            $pdf = PDF::loadView('pdf.monitoring', compact('monitoring', 'qrImage'));
            $pdfOutput = $pdf->output();
            $pdfBase64 = base64_encode($pdfOutput);

            return $this->sendSuccess($pdfBase64, 'PDF');
        } catch (\Exception $exception) {
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


}
