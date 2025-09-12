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
            $filters = request()->only(['status', 'type','category', 'region_id', 'district_id', 'id','monitoring_type']);
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
            $filters = request()->only(['status', 'type','category', 'region_id', 'district_id', 'id','monitoring_type']);
            $data = $this->service->count($this->user, $this->roleId, $filters);
            return $this->sendSuccess($data, 'Count');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }



    private function getGroupedCounts($query, $selectRaw, $groupBy, $startDate = null, $endDate = null)
    {
        $decisionsSub = DB::table('decisions')
            ->selectRaw("
            id,
            COUNT(*) AS decision_count,
            SUM(CASE WHEN decision_status = 12 THEN 1 ELSE 0 END) AS paid_count,
            SUM(CASE WHEN decision_status != 12 OR decision_status IS NULL THEN 1 ELSE 0 END) AS unpaid_count,
            SUM(main_punishment_amount::numeric) AS total_amount,
            SUM(CASE WHEN decision_status = 12 THEN main_punishment_amount::numeric ELSE 0 END) AS paid_amount,
            SUM(CASE WHEN decision_status != 12 OR decision_status IS NULL THEN main_punishment_amount::numeric ELSE 0 END) AS unpaid_amount
        ")
            ->where('project_id', FineType::APARTMENT)
            ->groupBy('id'); // ✅ endi id bo‘yicha group

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
            ->leftJoinSub($decisionsSub, 'dec', function ($join) {
                $join->on('monitorings.decision_id', '=', 'dec.id'); // ✅ belongsTo
            })
            ->leftJoinSub($violationsSub, 'vio', function ($join) {
                $join->on('vio.monitoring_id', '=', 'monitorings.id');
            })
            ->selectRaw("
            $selectRaw,
            monitorings.monitoring_status_id,

            COUNT(DISTINCT monitorings.id) AS count,

            COALESCE(SUM(dec.decision_count), 0) AS decision_count,
            COALESCE(SUM(dec.paid_count), 0) AS paid_count,
            COALESCE(SUM(dec.unpaid_count), 0) AS unpaid_count,
            COALESCE(SUM(dec.total_amount), 0) AS total_amount,
            COALESCE(SUM(dec.paid_amount), 0) AS paid_amount,
            COALESCE(SUM(dec.unpaid_amount), 0) AS unpaid_amount,

            COALESCE(SUM(vio.has_deadline), 0) AS fix_formed,
            SUM(CASE WHEN monitorings.step = 4 THEN 1 ELSE 0 END) AS fix_done,
            SUM(CASE WHEN monitorings.is_administrative = TRUE THEN 1 ELSE 0 END) AS fix_administrative,
            SUM(CASE WHEN monitorings.send_court = TRUE THEN 1 ELSE 0 END) AS fix_court,
            SUM(CASE WHEN monitorings.send_mib = TRUE THEN 1 ELSE 0 END) AS fix_mib,
            SUM(CASE WHEN monitorings.send_chora = TRUE THEN 1 ELSE 0 END) AS fixed
        ")
            ->groupBy(...$groupBy)
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

            $protocolCounts = $this->getGroupedCounts(
                query: Monitoring::query(),
                selectRaw: $group . ' as group_id',
                groupBy: [$group, 'monitorings.monitoring_status_id'],
                startDate: $startDate,
                endDate: $endDate
            )->groupBy('group_id');

            $data = $regions->map(function ($region) use ($userCounts, $protocolCounts) {
                $regionId        = $region->id;
                $regionProtocols = $protocolCounts->get($regionId, collect());

                $sumByStatus = fn($statuses) =>
                $regionProtocols->whereIn('monitoring_status_id', (array)$statuses)->sum('count');

                return [
                    'id'                  => $region->id,
                    'name'                => $region->name_uz,
                    'inspector_count'     => $userCounts->get($regionId, 0),
                    'all_monitorings'     => $regionProtocols->sum('count'),
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
                    'fix_formed'          => $regionProtocols->sum('fix_formed'),        // violations.deadline IS NOT NULL
                    'fix_done'            => $regionProtocols->sum('fix_done'),          // step = 4
                    'fix_administrative'  => $regionProtocols->sum('fix_administrative'),// is_administrative = true
                    'fix_court'           => $regionProtocols->sum('fix_court'),         // send_court = true
                    'fix_mib'             => $regionProtocols->sum('fix_mib'),           // send_mib = true
                    'fixed'               => $regionProtocols->sum('fixed'),             // send_chora = true

                    'decision_count'      => $regionProtocols->sum('decision_count'),
                    'paid_count'          => $regionProtocols->sum('paid_count'),
                    'unpaid_count'        => $regionProtocols->sum('unpaid_count'),

                    'total_amount'        => $regionProtocols->sum('total_amount'),
                    'paid_amount'         => $regionProtocols->sum('paid_amount'),
                    'unpaid_amount'       => $regionProtocols->sum('unpaid_amount'),
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
        }catch (\Exception $exception) {
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
            return  $this->sendSuccess(FineResource::make($monitoring->fine), 'Success');
        }catch (\Exception $exception){
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

    public function pdf($id):JsonResponse
    {
        try {
            $monitoring = Monitoring::find($id);
            $domain = URL::to('/regulation-info').'/'.$id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
            $pdf = PDF::loadView('pdf.monitoring', compact('monitoring', 'qrImage'));
            $pdfOutput = $pdf->output();
            $pdfBase64 = base64_encode($pdfOutput);

            return $this->sendSuccess($pdfBase64, 'PDF');
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


}
