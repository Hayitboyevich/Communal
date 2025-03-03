<?php

if (!function_exists('pagination')) {
    function pagination(object $model)
    {
        if ($model)
            $data = [
                'lastPage' => $model->lastPage(),
                'total' => $model->total(),
                'perPage' => $model->perPage(),
                'currentPage' => $model->currentPage(),
            ];
        else
            $data = [
                'lastPage' => 0,
                'total' => 0,
                'perPage' => 0,
                'currentPage' => 0,
            ];
        return $data;
    }
}
