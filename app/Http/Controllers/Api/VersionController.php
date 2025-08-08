<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VersionController extends BaseController
{
    public function index($id = null): JsonResponse
    {
        try {
            $version = AppVersion::query()->select(['id','version'])->where('project_id', $id)->first();
            return $this->sendSuccess($version, 'Success');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function update($id, Request $request): JsonResponse
    {
        try {
            $version = AppVersion::query()->select(['id', 'version'])->updateOrCreate(['project_id' => $id], ['version' => $request->post('version')]);
            return $this->sendSuccess($version, 'Success');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
