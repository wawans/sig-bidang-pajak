<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class LayerGroupFilter extends ModelFilter
{
    use Concerns\Searchable;

    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    /**
     * Get searchable fields array
     *
     * @return array<string>
     */
    protected function getSearchable(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
