<?php

namespace Modules\Water\Repositories;

use Modules\Water\Contracts\CardRepositoryInterface;
use Modules\Water\Http\Requests\CardRequest;
use Modules\Water\Models\Card;

class CardRepository implements CardRepositoryInterface
{

    public function findById($id)
    {
        return Card::query()->findOrFail($id);
    }

    public function all($userId)
    {
        return Card::query()->where('user_id', $userId);
    }

    public function create(CardRequest $request)
    {
        try {
             return Card::query()->create($request->validated());
        }catch (\Exception $exception){
            throw  $exception;
        }
    }

    public function changeCard($id, $userId)
    {
        Card::query()->where('user_id', $userId)->where('id', '!=', $id)->update(['status' => false]);

        return tap(Card::query()->findOrFail($id), function ($card) {
            $card->update(['status' => true]);
        });
    }

}
