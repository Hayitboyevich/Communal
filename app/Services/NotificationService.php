<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class NotificationService
{
    public function __construct(private Notification $notification)
    {
    }

    /**
     * @throws Throwable
     */
    public function createNotification($data, $userId): void
    {
        DB::transaction(function () use ($data, $userId) {
            $this->notification->create([
                'user_id' => $userId,
                'data' => $data
            ]);
        });
    }

    public function listNotification()
    {
        return $this->notification->where('user_id', 485)->with('user')->orderBy('created_at', 'desc')->get();
    }

    /**
     * @throws Throwable
     */
    public function markRead(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $this->notification->whereIn('id', $ids)->update(['is_read' => true, 'read_at' => now()]);
        });
    }
}
