<?php

namespace App\Repositories\Color;

use App\Repositories\Concerns\WithTable;
use App\Repositories\Repository;

/**
 * \App\Repositories\Color\ColorItemRepository
 *
 * @property \App\Models\Color\ColorItem $model
 *
 * @method \Illuminate\Database\Eloquent\Builder|\App\Models\Color\ColorItem query()
 * @method \App\Models\Color\ColorItem update(array $attributes, \App\Models\Color\ColorItem $colorItem)
 */
class ColorItemRepository extends Repository
{
    use WithTable;

    /**
     * Create a new repository instance.
     */
    public function __construct(protected \App\Models\Color\ColorItem $model) {}

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return \App\Models\Color\ColorItem
     */
    public function store($attributes)
    {
        return $this->create($attributes);
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  \App\Models\Color\ColorItem  $colorItem
     * @return \App\Models\Color\ColorItem
     */
    public function edit($attributes, $colorItem)
    {
        return $this->update($attributes, $colorItem);
    }

    /**
     * Delete the model from the database.
     *
     * @param  \App\Models\Color\ColorItem  $colorItem
     * @return bool|null|void
     */
    public function destroy($colorItem)
    {
        return $this->delete($colorItem);
    }
}
