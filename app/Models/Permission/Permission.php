<?php

namespace App\Models\Permission;

use App\ModelFilters\Permission\PermissionFilter;
use EloquentFilter\Filterable;
use Spatie\Permission\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    use Filterable;

    /**
     * Define Model Filter.
     *
     * @return string|null
     */
    public function modelFilter()
    {
        return $this->provideFilter(PermissionFilter::class);
    }
}
