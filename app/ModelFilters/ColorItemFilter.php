<?php

namespace App\ModelFilters;

use App\Models\Color\ColorItem;
use EloquentFilter\ModelFilter;

/** @mixin ColorItem */
class ColorItemFilter extends ModelFilter
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
            'uuid',
            'color_group_id',
            'label',
            'color',
        ];
    }

    public function colorGroupId($value)
    {
        return $this->where('color_group_id', $value);
    }
}
