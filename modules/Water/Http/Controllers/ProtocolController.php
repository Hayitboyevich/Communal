<?php

namespace Modules\Water\Http\Controllers;

use App\Http\Controllers\BaseController;
use Modules\Water\Services\ProtocolService;

class ProtocolController extends BaseController
{

    public function __construct(
        protected ProtocolService $service
    ){}

    public function index()
    {
        try {
            $protocols = $this->service->getAll();

        } catch (\Exception $exception){
            return $this->sendError('Xatolik mavjud', $exception->getMessage());
        }
    }
}
