<?php

namespace App\Repositories;

use App\Repositories\Concerns\WithTable;

/**
 * \App\Repositories\StyleRepository
 *
 * @property \App\Models\Style $model
 *
 * @method \Illuminate\Database\Eloquent\Builder|\App\Models\Style query()
 * @method \App\Models\Style update(array $attributes, \App\Models\Style $style)
 */
class StyleRepository extends Repository
{
    use WithTable;

    /**
     * Create a new repository instance.
     */
    public function __construct(protected \App\Models\Style $model) {}

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return \App\Models\Style
     */
    public function store($attributes)
    {
        return $this->create($attributes);
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  \App\Models\Style  $style
     * @return \App\Models\Style
     */
    public function edit($attributes, $style)
    {
        return $this->update($attributes, $style);
    }

    /**
     * Delete the model from the database.
     *
     * @param  \App\Models\Style  $style
     * @return bool|null|void
     */
    public function destroy($style)
    {
        return $this->delete($style);
    }
}
