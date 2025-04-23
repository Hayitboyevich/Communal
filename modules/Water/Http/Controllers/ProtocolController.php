<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProtocolChangeRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Water\Const\ProtocolHistoryType;
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
}
