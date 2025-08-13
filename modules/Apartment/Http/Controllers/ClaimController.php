<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Modules\Apartment\Http\Requests\ClaimRequest;
use Modules\Apartment\Http\Requests\ClaimUpdateRequest;
use Modules\Apartment\Http\Resources\ClaimResource;
use Modules\Apartment\Services\ClaimService;

class ClaimController extends BaseController
{
    public function __construct(protected ClaimService $service)
    {
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['status']);
            $claims = $id
                ? $this->service->findById($id)
                : $this->service->all($this->user, $this->roleId, $filters)->paginate(request('per_page', 15));

            $resource = $id
                ? ClaimResource::make($claims)
                : ClaimResource::collection($claims);

            return $this->sendSuccess(
                $resource,
                $id ? 'Claim retrieved successfully.' : 'Claims retrieved successfully.',
                $id ? null : pagination($claims)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(ClaimRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request);
            return $this->sendSuccess(ClaimResource::make($data), 'Claim created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function update($id, ClaimUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->service->update($id, $request);
            return $this->sendSuccess(ClaimResource::make($data),  'Protocol updated successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function count(): JsonResponse
    {
        try {
            return  $this->sendSuccess($this->service->count(), 'count');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function cadastr(): JsonResponse
    {
        try {
            $cadNumber = request('cad_number');
            $response = Http::withBasicAuth(config('apartment.cadastr.login'), config('apartment.cadastr.password'))
                ->post(config('apartment.cadastr.url'), [
                    'cad_num' => $cadNumber,
                ]);

            if ($response->successful()) {
                $data = $response->json()['result']['data'];

                $region = Region::query()->where('soato', $data['region_id'])->first();
                $district = District::query()->where('soato', $data['district_id'])->first();
                $meta = [
                    'region' => RegionResource::make($region),
                    'district' => DistrictResource::make($district),
                    'name' => $data['name'],
                    'subjects' => $data['subjects'],
                    'address' => $data['address'],
                    'cad_number' => $data['cad_number'],
                ];

                return $this->sendSuccess($meta, 'Cadastr');

            }
            return $this->sendError('Xatolik yuz berdi', 'Cadastr');

        } catch (\Exception $exception) {
            return $this->sendError('Kadastr bilan xatolik yuz berdi', $exception->getMessage());
        }
    }
}
