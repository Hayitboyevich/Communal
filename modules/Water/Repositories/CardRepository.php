<?php

namespace Modules\Water\Repositories;

use Modules\Water\Contracts\CardRepositoryInterface;
use Modules\Water\Http\Requests\CardRequest;
use Modules\Water\Models\Card;

class CardRepository implements CardRepositoryInterface
{
    public function getInfo()
    {

    }



    public function register()
    {

    }

    public function verify()
    {

    }

    public function create(CardRequest $request)
    {
        try {
             return Card::query()->create($request->validated());
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

}
