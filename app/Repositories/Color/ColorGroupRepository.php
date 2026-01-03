<?php

namespace App\Repositories\Color;

use App\Repositories\Concerns\WithTable;
use App\Repositories\Repository;

/**
 * \App\Repositories\Color\ColorGroupRepository
 *
 * @property \App\Models\Color\ColorGroup $model
 *
 * @method \Illuminate\Database\Eloquent\Builder|\App\Models\Color\ColorGroup query()
 * @method \App\Models\Color\ColorGroup update(array $attributes, \App\Models\Color\ColorGroup $colorGroup)
 */
class ColorGroupRepository extends Repository
{
    use WithTable;

    /**
     * Create a new repository instance.
     */
    public function __construct(protected \App\Models\Color\ColorGroup $model) {}

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return \App\Models\Color\ColorGroup
     */
    public function store($attributes)
    {
        return $this->create($attributes);
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  \App\Models\Color\ColorGroup  $colorGroup
     * @return \App\Models\Color\ColorGroup
     */
    public function edit($attributes, $colorGroup)
    {
        return $this->update($attributes, $colorGroup);
    }

    /**
     * Delete the model from the database.
     *
     * @param  \App\Models\Color\ColorGroup  $colorGroup
     * @return bool|null|void
     */
    public function destroy($colorGroup)
    {
        return $this->delete($colorGroup);
    }
}
