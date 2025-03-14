<?php

namespace Modules\Water\Services;

use Modules\Water\Contracts\CardRepositoryInterface;

class CardService
{
    public function __construct(protected CardRepositoryInterface $repository){}

    public function register($data)
    {
        try {
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
