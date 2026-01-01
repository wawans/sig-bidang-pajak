<?php

namespace App\Http\Controllers\Concerns;

use App\Support\Response\ApiResponse;

trait ApiTable
{
    public function table()
    {
        $data = $this->repository->table(method_exists($this, 'tableQuery') ? $this->tableQuery(request()) : request());

        if (method_exists($this, 'gate')) {
            $data->put('gate', $this->gate());
        }

        return ApiResponse::make($data);
    }
}
