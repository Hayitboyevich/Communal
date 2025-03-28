<?php

namespace Modules\Water\Contracts;

use Modules\Water\Http\Requests\CardRequest;

interface CardRepositoryInterface
{
    public function getInfo();

    public function register();

    public function verify();

    public function create(CardRequest $request);
}
