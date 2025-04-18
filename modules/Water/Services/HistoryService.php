<?php

namespace Modules\Water\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Water\Const\ProtocolHistoryType;
use Modules\Water\Contracts\HistoryRepositoryInterface;
use Modules\Water\Repositories\HistoryRepository;

class HistoryService
{

    protected HistoryRepositoryInterface $repository;

    public function __construct($tableName)
    {
        $this->repository = new HistoryRepository($tableName);
    }

    public function createHistory(int $modelId, int $status, int $type, ?string $date, ?string $comment = "", mixed $additionalInfo = null): int
    {
        $content = match ($type) {
            ProtocolHistoryType::CREATE_FIRST,
            ProtocolHistoryType::CREATE_SECOND,
            ProtocolHistoryType::CREATE_THIRD,
            => $this->shapeTaskContent(
                status: $status,
                comment: $comment,
                date: $date,
                additionalInfo: $additionalInfo
            ),
            default => null,
        };

        if (!$content) {
            return false;
        }

        return $this->repository->createHistory(modelId: $modelId, content: $content, type: $type);
    }

    private function shapeTaskContent(int $status, string $comment, ?string $date, mixed $additionalInfo): array
    {
        $user = Auth::user();
        return [
            'user' => Auth::check() ? $user->id : "",
            'role' => $user ? (int)$user->getRoleFromToken() : null,
            'date' => ($date == null) ? now() : $date,
            'status' => $status,
            'comment' => $comment,
            'additionalInfo' => $additionalInfo
        ];
    }
}
