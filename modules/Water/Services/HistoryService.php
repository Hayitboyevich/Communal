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

    public function createHistory(int $guid, int $status, int $type, ?string $date, ?string $comment = "", mixed $additionalInfo = null): int
    {
        $content = match ($type) {
            ProtocolHistoryType::CREATE_FIRST,
            ProtocolHistoryType::CREATE_SECOND,
            ProtocolHistoryType::CREATE_THIRD,
            ProtocolHistoryType::CONFIRM_DEFECT,
            ProtocolHistoryType::REJECT_DEFECT,
            ProtocolHistoryType::ATTACH_INSPECTOR,
            ProtocolHistoryType::REJECT,
            ProtocolHistoryType::CONFIRM_NOT_DEFECT,
            ProtocolHistoryType::NOT_DEFECT,
            ProtocolHistoryType::CONFIRM_RESULT,
            ProtocolHistoryType::REJECT_RESULT,
            ProtocolHistoryType::SEND_HMQO,
            ProtocolHistoryType::CONFIRMED,
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

        return $this->repository->createHistory(guid: $guid, content: $content, type: $type);
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

    public function createImages($id, $data)
    {

    }

    public function createFiles($id, $data)
    {

    }
}
