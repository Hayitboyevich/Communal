<?php

namespace Modules\Water\Contracts;

interface DecisionRepositoryInterface
{
    public function get(
        string $series,
        string $number,
        int $projectId
    );

    public function update(?array $data);

    public function create(?array $data);
}
