<?php

namespace Modules\Water\Contracts;

interface DecisionRepositoryInterface
{
    public function get(
        string $series,
        string $number
    );

    public function update(
        string $series,
        string $number,
        array $data
    );

    public function create(?array $data);
}
