<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\BaseController;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Enums\ApartmentHiddenEconomyTypeEnum;
use Modules\Apartment\Http\Requests\ApartmentHiddenEconomyRequest;
use Modules\Apartment\Http\Resources\ApartmentResource;
use Modules\Apartment\Http\Resources\CompanyResource;
use Modules\Apartment\Http\Resources\MonitoringBaseResource;
use Modules\Apartment\Http\Resources\MonitoringStatusResource;
use Modules\Apartment\Http\Resources\MonitoringTypeResource;
use Modules\Apartment\Http\Resources\PlaceResource;
use Modules\Apartment\Http\Resources\ViolationTypeResource;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;
use Modules\Apartment\Models\MonitoringBase;
use Modules\Apartment\Models\MonitoringStatus;
use Modules\Apartment\Models\MonitoringType;
use Modules\Apartment\Models\ViolationType;
use Modules\Apartment\Models\WorkType;

class InformationController extends BaseController
{
    public function place($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(PlaceResource::collection(Place::query()->where('monitoring_type_id', $id)->get()), 'Place list');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function violationType($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(ViolationTypeResource::collection(ViolationType::query()->where('place_id', $id)->get()), 'Violation type list');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function monitoringType($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(MonitoringTypeResource::collection(MonitoringType::all()), 'Monitoring type list');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function monitoringBase($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(MonitoringBaseResource::collection(MonitoringBase::all()), 'Monitoring base list');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function company($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['district_id', 'region_id', 'company_name']);
            $companies = Company::query()
                ->when(!empty($filters['district_id']), function ($query) use ($filters) {
                    $query->where('district_id', $filters['district_id']);
                })
                ->when(!empty($filters['region_id']), function ($query) use ($filters) {
                    $query->where('region_id', $filters['region_id']);
                })
                ->when(!empty($filters['company_name']), function ($query) use ($filters) {
                    $query->where('company_name', 'like', '%' . $filters['company_name'] . '%');
                })
                ->get();
            return $this->sendSuccess(CompanyResource::collection($companies), 'Company list');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function apartment($id = null): JsonResponse
    {
        try {
            $apartments = Apartment::query()->where('company_id', request('company_id'))->get();
            return $this->sendSuccess(ApartmentResource::collection($apartments), 'Apartment list');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function monitoringStatus($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(MonitoringStatusResource::collection(MonitoringStatus::all()), 'Monitoring Status List');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function workType($id = null): JsonResponse
    {
        try {
            $data = WorkType::all();
            return $this->sendSuccess($data, 'Work Type List');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function fines()
    {
        try {
            $inn = request('inn');

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function apartmentHiddenEconomy(ApartmentHiddenEconomyRequest $request, $id = null)
    {
        $user = $this->user;
        $roleId = $this->roleId;
        if (!$id) {
            $validated = $request->validated();
            $monitoring_type_id = $validated['monitoring_type_id'];
            $per_page = $validated['per_page'] ?? 10;
            $page = $validated['page'] ?? 1;
            $place_id = $validated['place_id'] ?? null;
            if ($roleId == UserRoleEnum::APARTMENT_MANAGER->value) {
                $apartments = Apartment::query()
                    ->when(!empty($validated['region_id']), fn($q) => $q->whereHas('company',
                        fn($q) => $q->where('region_id', $validated['region_id'])))
                    ->when(!empty($validated['district_id']), fn($q) => $q->whereHas('company',
                        fn($q) => $q->where('district_id', $validated['district_id'])))
                    ->when(!empty($validated['company_id']), fn($q) => $q->where('company_id', $validated['company_id']))
                    ->when(!empty($validated['home_id']), fn($q) => $q->where('home_id', $validated['home_id']))
                    ->with(['monitorings' => function ($query) use ($place_id, $validated, $monitoring_type_id) {
                        $query->when($monitoring_type_id == 1, function ($query) use ($monitoring_type_id) {
                            $query->where('monitoring_type_id', $monitoring_type_id)
                                ->whereHas('regulation', function ($query) {
                                    $query->whereIn('place_id', [1, 2]);
                                })->with('regulation');
                        })->when($monitoring_type_id == 2, function ($query) use ($monitoring_type_id, $validated, $place_id) {
                            $query->where('monitoring_type_id', $monitoring_type_id)
                                ->whereHas('regulation', function ($query) use ($place_id) {
                                    $query->whereIn('place_id', $place_id);
                                })->with('regulation');
                        });
                    }, 'company.region', 'company.district',
                        'monitorings.monitoringType', 'monitorings.status',
                        'monitorings.base', 'monitorings.user', 'monitorings.documents',
                        'apartmentHiddenEconomy' => function ($query) use ($place_id, $monitoring_type_id) {
                            $query->when($monitoring_type_id == 1, function ($query) use ($monitoring_type_id) {
                                $query->where('monitoring_type_id', $monitoring_type_id);
                            })->when(!empty($place_id) && in_array(8, $place_id) && in_array(9, $place_id), function ($query) use ($monitoring_type_id, $place_id) {
                                $query->where(['monitoring_type_id' => $monitoring_type_id, 'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::TOM->value]);
                            })->when(!empty($place_id) && in_array(10, $place_id), function ($query) use ($monitoring_type_id, $place_id) {
                                $query->where(['monitoring_type_id' => $monitoring_type_id, 'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::FASAD->value]);
                            });
                        }])->paginate($per_page, ['*'], 'page', $page);
                return $this->sendSuccess($apartments->items(), 'Apartment list', meta: pagination($apartments));
            } elseif ($roleId == UserRoleEnum::APARTMENT_INSPECTOR->value) {
                $apartments = Apartment::query()->whereHas('apartmentHiddenEconomy', function ($query) use ($place_id, $user) {
                    $query->where('user_id', $user->id);
                })
                    ->when(!empty($validated['type']), fn($q) => $q->whereHas('monitoring',
                        fn($q) => $q->where('type', $validated['type'])))
                    ->when(!empty($validated['is_administrative']), fn($q) => $q->whereHas('monitoring',
                        fn($q) => $q->where('is_administrative', $validated['is_administrative'])))
                    ->when(!empty($validated['district_id']), fn($q) => $q->whereHas('company',
                        fn($q) => $q->where('district_id', $validated['district_id'])))
                    ->when(!empty($validated['district_id']), fn($q) => $q->where('home_id',
                        fn($q) => $q->where('home_id', $validated['home_id'])))
                    ->when(!empty($validated['street_name']), fn($q) => $q->where('street_name',
                        fn($q) => $q->where('street_name', 'ILIKE', "%{$validated['street_name']}%")))
                    ->with(['company.region', 'company.district', 'apartmentHiddenEconomy' => function ($query) use ($place_id, $user) {
                        $query->where('user_id', $user->id);

                        if (is_null($place_id)) {
                            $query->where('monitoring_type_id', 1);
                        } elseif (in_array(8, $place_id) && in_array(9, $place_id)) {
                            $query->where([
                                'monitoring_type_id' => 2,
                                'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::TOM->value,
                            ]);
                        } elseif (in_array(10, $place_id)) {
                            $query->where([
                                'monitoring_type_id' => 2,
                                'hidden_economy_type' => ApartmentHiddenEconomyTypeEnum::FASAD->value,
                            ]);
                        }
                    }, 'apartmentHiddenEconomy.monitoringType',
                        'apartmentHiddenEconomy.monitoring.status'])
                    ->orderBy('id', 'desc')
                    ->paginate($per_page, ['*'], 'page', $page);
                $apartments->getCollection()->transform(function ($apartment) {
                    $apartment->setRelation(
                        'apartmentHiddenEconomy',
                        $apartment->apartmentHiddenEconomy->first()
                    );
                    return $apartment;
                });
                return $this->sendSuccess($apartments->items(), 'Apartment list', meta: pagination($apartments));
            }

        }
        return $this->sendSuccess(Apartment::query()->with(['company.region', 'company.district', 'apartmentHiddenEconomy.monitoringType'])->where('home_id', $id)->first(), 'Apartment retrieved successfully.');
    }

}
