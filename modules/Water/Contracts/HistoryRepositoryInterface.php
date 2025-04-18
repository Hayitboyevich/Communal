<?php

namespace Modules\Water\Contracts;

interface HistoryRepositoryInterface
{
    public function getHistoryList(int $guId);
    public function getFilteredList(int $guId, string $jsonColumn, $needle);
    public function getHistory(int $guId);
    public function createHistory(int $guid, array $content, int $type);
}
