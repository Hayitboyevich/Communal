<?php

namespace Modules\Water\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Contracts\HistoryRepositoryInterface;
use Modules\Water\Models\ProtocolHistory;
use Modules\Water\Repositories\HistoryRepository;

class HistoryService
{

    protected HistoryRepositoryInterface $repository;

    public function __construct($tableName)
    {
        $this->repository = new HistoryRepository($tableName);
    }

    public function createHistory(int $guid, int $status, int $type, ?string $date, ?string $comment = "", mixed $additionalInfo = null): int
    {
        $content = $this->shapeTaskContent(
            status: $status,
            comment: $comment,
            date: $date,
            additionalInfo: $additionalInfo
        );

        if (!$content) {
            return false;
        }

        return $this->repository->createHistory(guid: $guid, content: $content, type: $type);
    }

    private function shapeTaskContent(int $status, string $comment, ?string $date, mixed $additionalInfo): array
    {
        $user = Auth::user();
        return [
            'user' => $user->type == 1 ? null : (Auth::check() ? $user->id : ""),
            'role' => $user->type == 1 ? null : (int)$user->getRoleFromToken(),
            'date' => ($date == null) ? now() : $date,
            'status' => $status,
            'comment' => $comment,
            'additionalInfo' => $additionalInfo
        ];
    }
}
