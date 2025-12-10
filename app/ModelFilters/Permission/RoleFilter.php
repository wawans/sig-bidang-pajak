<?php

namespace App\ModelFilters\Permission;

use App\ModelFilters\Concerns\Searchable;
use App\Models\Permission\Role;
use EloquentFilter\ModelFilter;

/** @mixin Role */
class RoleFilter extends ModelFilter
{
    use Searchable;

    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    /**
     * Get searchable fields array
     */
    protected function getSearchable(): array
    {
        return [
            'id',
            'name',
            'guard_name',
        ];
    }
}
