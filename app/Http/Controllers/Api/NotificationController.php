<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationMarkReadRequest;
use App\Services\NotificationService;

class NotificationController extends BaseController
{

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function sendList()
    {
        return $this->sendSuccess($this->notificationService->listNotification(), 'Success');
    }

    public function read(NotificationMarkReadRequest $request)
    {
        $validated = $request->validated();
        $this->notificationService->markRead($validated['ids']);
        return $this->sendSuccess(null, 'Success');
    }
}
