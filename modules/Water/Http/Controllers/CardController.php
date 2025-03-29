<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Http\Requests\CardRequest;
use Modules\Water\Http\Resources\CardResource;
use Modules\Water\Services\CardService;

class CardController extends BaseController
{
    public function __construct(protected CardService $service){
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $cards = $id
                ? $this->service->findById($id)
                : $this->service->getAll($this->user)->paginate(request('per_page', 15));

            $resource = $id
                ? CardResource::make($cards)
                : CardResource::collection($cards);

            return $this->sendSuccess(
                $resource,
                $id ? 'Card retrieved successfully.' : 'Cards retrieved successfully.',
                $id ? null : pagination($cards)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function register(): JsonResponse
    {
        try {
            $data = $this->service->register(request()->all());

            return $this->sendSuccess($data, 'Ok');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function verify(): JsonResponse
    {
        try {
            $data = $this->service->verify(request()->all());
            return $this->sendSuccess($data, 'Ok');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(CardRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request);
            return $this->sendSuccess(CardResource::make($data), 'Ok');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function change(): JsonResponse
    {
        try {
            $data = $this->service->change(request('card_id'), $this->user);
            return $this->sendSuccess(CardResource::make($data), 'Ok');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


}
