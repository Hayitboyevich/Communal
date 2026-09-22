<?php

namespace App\Traits;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HandlesExceptions
{
    /**
     * @throws ServerException
     * @throws NotFoundException
     */
    protected function safeCall(callable $callable): mixed
    {
        try {
            return $callable();
        } catch (ModelNotFoundException $e) {
            report($e);
            throw new NotFoundException('Data not found');
        } catch (\Throwable $exception) {
            report($exception);
            throw new ServerException($exception->getMessage());
        }
    }
}
