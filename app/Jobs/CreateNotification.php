<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60, 180];

    public int $timeout = 30;
    /**
     * Create a new job instance.
     */
    public function __construct(public array $data, public int $userId)
    {
        $this->onQueue('notify');
        $this->afterCommit();
    }

    /**
     * Execute the job.
     * @throws Throwable
     */
    public function handle(NotificationService $notificationService): void
    {
        $notificationService->createNotification($this->data, $this->userId);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Notification yaratilmadi', [
            'user_id' => $this->userId,
            'message' => $e->getMessage(),
            'data' => $this->data,
        ]);
    }
}
