<?php

namespace Modules\Water\Services;

use Modules\Water\Contracts\CardRepositoryInterface;
use Modules\Water\Http\Requests\CardRequest;

class CardService
{
    public function __construct(protected CardRepositoryInterface $repository){}

    public function register($data)
    {
        try {
            return postData(config('water.card.register'), $data);
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function verify($data)
    {
        try {
            return postData(config('water.card.verify'), $data);
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function create(CardRequest $request)
    {
        try {
            return $this->repository->create($request);
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function findById($id)
    {
        return $this->repository->findById($id);
    }

    public function getAll($user)
    {
       return $this->repository->all($user->id);
    }

    public function change($id, $user)
    {
        return $this->repository->changeCard($id, $user->id);
    }
}
