<?php

namespace Modules\Water\Contracts;

use Modules\Water\Http\Requests\CardRequest;

interface CardRepositoryInterface
{

    public function findById($id);
    public function all($userId);

    public function changeCard($id, $userId);

    public function create(CardRequest $request);
}
