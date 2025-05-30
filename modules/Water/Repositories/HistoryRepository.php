<?php

namespace Modules\Water\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Water\Contracts\HistoryRepositoryInterface;

class HistoryRepository implements HistoryRepositoryInterface
{
    public function __construct(protected  $table)
    {
    }

    public function getHistoryList(int $guId)
    {
        return DB::table($this->table)->where('gu_id', $guId)->where('type', LogType::TASK_HISTORY)->orderBy('id', 'asc')->get([
            'id',
            'gu_id',
            'content',
            'created_at'
        ]);
    }

    public function getFilteredList(int $guId, string $jsonColumn, $needle)
    {
        return DB::table($this->table)->where('gu_id', $guId)
            ->where("content->$jsonColumn", $needle)
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'guid',
                'content',
                'created_at'
            ]);
    }

    public function getHistory(int $id)
    {
        return DB::table($this->table)->where('id', $id)->where('type', LogType::TASK_HISTORY)->first([
            'id',
            'guid',
            'content',
            'created_at'
        ]);
    }

    public function createHistory(int $guid, array $content, int $type): int
    {
        return DB::table($this->table)->insertGetId([
            'guid' => $guid,
            'content' => json_encode($content),
            'type' => $type,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

}
