<?php

namespace App\Models\Permission;

use App\ModelFilters\Permission\RoleFilter;
use EloquentFilter\Filterable;
use Spatie\Permission\Models\Role as RoleModel;

class Role extends RoleModel
{
    use Filterable;

    /**
     * Define Model Filter.
     *
     * @return string|null
     */
    public function modelFilter()
    {
        return $this->provideFilter(RoleFilter::class);
    }
}
