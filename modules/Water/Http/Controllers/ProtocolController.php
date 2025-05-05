<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProtocolChangeRequest;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Enums\ProtocolStatusEnum;
use Modules\Water\Http\Requests\ProtocolFirstStepRequest;
use Modules\Water\Http\Requests\ProtocolSecondStepRequest;
use Modules\Water\Http\Requests\ProtocolThirdStepRequest;
use Modules\Water\Http\Resources\ProtocolListResource;
use Modules\Water\Http\Resources\ProtocolResource;
use Modules\Water\Models\Protocol;
use Modules\Water\Services\ProtocolService;

class ProtocolController extends BaseController
{

    public function __construct(
        protected ProtocolService $service
    ){
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['status', 'protocol_number', 'district_id', 'region_id', 'protocol_type', 'type', 'attach', 'category']);
            $protocols = $id
                ? $this->service->findById($id)
                : $this->service->getAll($this->user, $this->roleId, $filters)->paginate(request('per_page', 15));

            $resource = $id
                ? ProtocolResource::make($protocols)
                : ProtocolListResource::collection($protocols);

            return $this->sendSuccess(
                $resource,
                $id ? 'Protocol retrieved successfully.' : 'Protocols retrieved successfully.',
                $id ? null : pagination($protocols)
            );

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
        }
    }

    public function attach(Request $request): JsonResponse
    {
        try {
           $protocol = $this->service->attach($request->all(), $this->user, $this->roleId);
           return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol attached successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


    public function createFirst(ProtocolFirstStepRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $protocol = $this->service->create($request->except('images'));
            $this->service->saveImages($protocol, $request['images']);

            DB::commit();
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createSecond(?int $id, ProtocolSecondStepRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $protocol = $this->service->update($this->user, $this->roleId,$id, $request->except('files', 'additional_files'), ProtocolHistoryType::CREATE_SECOND);
            $this->service->saveFiles($protocol, $request['files']);
            $this->service->uploadFiles($protocol, 'additional_files', $request['additional_files']);
            DB::commit();
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createThird(?int $id, ProtocolThirdStepRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $protocol = $this->service->update($this->user, $this->roleId,$id, $request->except('image_files'), ProtocolHistoryType::CREATE_THIRD);
            $this->service->uploadFiles($protocol, 'image_files', $request['image_files']);
            DB::commit();
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function statusChange($id, ProtocolChangeRequest $request): JsonResponse
    {
        try {
            $protocol = $this->service->statusChange($id, $request);
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol status changed successfully.');
        }catch (\Exception $exception){
            return  $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function confirmDefect(): JsonResponse
    {
        try {
            $protocol = $this->service->confirmDefect($this->user, $this->roleId, request('id'));
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol confirmed successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
    public function rejectDefect(): JsonResponse
    {
        try {
            $protocol = $this->service->rejectDefect($this->user, $this->roleId, request()->all());
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol rejected successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }

    }

    public function confirmResult(): JsonResponse
    {
        try {
            $protocol = $this->service->confirmResult($this->user, $this->roleId, request('id'));
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol confirmed successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function rejectResult(): JsonResponse
    {
        try {
            $protocol = $this->service->rejectResult($this->user, $this->roleId, request()->all());
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol rejected successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function history($id): JsonResponse
    {
        try {
            return $this->sendSuccess($this->service->history($id), 'Object History');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function count(): JsonResponse
    {
        try {
            $filters = \request()->only(['status', 'category', 'type']);
            $data = $this->service->count($this->user, $this->roleId, $filters);
            return $this->sendSuccess($data, 'Count of protocols retrieved successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }

    }

    public function reject(): JsonResponse
    {
        try {
         $protocol = $this->service->reject($this->user, $this->roleId, request()->all());
         return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol rejected successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function pdf($id): JsonResponse
    {
        try {
            $protocol = Protocol::query()->findOrFail($id);
            $pdf = Pdf::loadView('pdf.protocol', compact(
                'protocol'
            ));
            $pdfOutput = $pdf->output();
            $pdfBase64 = base64_encode($pdfOutput);
            return $this->sendSuccess($pdfBase64, 'PDF');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function protocolReport($regionId = null): JsonResponse
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
                ->where('user_roles.role_id', UserRoleEnum::INSPECTOR->value)
                ->groupBy($group)
                ->pluck('count', $group);

            $protocolCounts = $this->getGroupedCounts(
                query: Protocol::query(),
                selectRaw:$group,
                groupBy:[$group, 'protocol_status_id', 'type'],
                startDate: $startDate,
                endDate: $endDate
            )->groupBy($group);

            $data = $regions->map(function ($region) use (
                $userCounts,
                $protocolCounts,

            ) {
                $regionId = $region->id;
                $regionProtocols = $protocolCounts->get($regionId, collect());

                return [
                    'id' => $region->id,
                    'name' => $region->name_uz,
                    'inspector_count' => $userCounts->get($regionId, 0),
                    'all_protocols' => $regionProtocols->sum('count'),
                    'defect_count' => $regionProtocols->whereNotIn('object_status_id', [ProtocolStatusEnum::ENTER_RESULT,ProtocolStatusEnum::NOT_DEFECT, ProtocolStatusEnum::CONFIRM_NOT_DEFECT, ProtocolStatusEnum::REJECTED])->sum('count'),
                    'remedy_count' => $regionProtocols->where('type', 2)->sum('count'),
                    'confirmed_count' => $regionProtocols->where('protocol_status_id', ProtocolStatusEnum::CONFIRMED)->sum('count'),
                    'administrative_count' => $regionProtocols->where('protocol_status_id', ProtocolStatusEnum::ADMINISTRATIVE)->sum('count'),
                    'hmqo_count' => $regionProtocols->where('protocol_status_id', ProtocolStatusEnum::HMQO)->sum('count'),

                ];
            });

            return $this->sendSuccess($data->values(), 'Data retrieved successfully');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    private function getGroupedCounts($query, $selectRaw, $groupBy, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if (\request('program_id')) {
            $query->where('program_id', \request('program_id'));
        }

        return $query
            ->selectRaw("$selectRaw, COUNT(*) as count")
            ->groupBy(...$groupBy)
            ->get();
    }
}
