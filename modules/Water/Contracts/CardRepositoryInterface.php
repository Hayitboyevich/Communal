<?php

namespace Modules\Water\Contracts;

interface CardRepositoryInterface
{
    public function getInfo();

    public function register();

    public function verify();
}
