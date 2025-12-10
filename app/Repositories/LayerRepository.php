<?php

namespace App\Repositories;

use App\Repositories\Concerns\WithTable;
use Illuminate\Support\Collection;

/**
 * \App\Repositories\LayerRepository
 *
 * @property \App\Models\Layer $model
 *
 * @method \Illuminate\Database\Eloquent\Builder|\App\Models\Layer query()
 * @method \App\Models\Layer update(array $attributes, \App\Models\Layer $layer)
 */
class LayerRepository extends Repository
{
    use WithTable;

    /**
     * Create a new repository instance.
     */
    public function __construct(protected \App\Models\Layer $model) {}

    public function toForm(\App\Models\Layer $model)
    {
        return [
            ...$this->toArray($model),
            'layer_group_id' => $model->member?->first()?->layer_group_id,
        ];
    }

    public function toShow(\App\Models\Layer $model)
    {
        $model->loadMissing(['defaultStyle', 'selectStyle']);

        return [
            ...$this->toArray($model),
            'layer_group_name' => $model->member?->first()?->group?->name,
        ];
    }

    /**
     * Create a new instance of the given model.
     *
     * @return \App\Models\Layer
     */
    public function store(array $attributes)
    {
        $attributes = collect($attributes);
        /** @var \App\Models\Layer $model */
        $model = $this->create($attributes->except([/* 'geometry', 'properties', */ 'layer_group_id'])->toArray());

        // if ($model->writeable) {
        //     $model->geometry = $attributes->get('geometry', 'geometry');
        //     $model->properties = $attributes->get('properties', []);
        //     $model->saveQuietly();
        // }

        $this->syncLayerGroup($attributes, $model);

        return $model;
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  \App\Models\Layer  $layer
     * @return \App\Models\Layer
     */
    public function edit($attributes, $layer)
    {
        $attributes = collect($attributes);

        $model = $this->update($attributes->except([/* 'geometry', 'properties', */ 'layer_group_id'])->toArray(), $layer);
        // $model->geometry = $attributes->get('geometry', 'geometry');
        // $model->properties = $attributes->get('properties', []);
        // $model->saveQuietly();

        $this->syncLayerGroup($attributes, $model);

        return $model;

    }

    protected function syncLayerGroup(Collection $attributes, \App\Models\Layer $model)
    {
        if ($attributes->has('layer_group_id')) {
            $new_group_id = $attributes->get('layer_group_id');
            $exist = $model->member->first();
            if ($exist) {
                $exist->layer_group_id = $new_group_id;
                $exist->save();
            } elseif (! blank($new_group_id)) {
                $model->member()->create([
                    'layer_group_id' => $new_group_id,
                    'layer_id' => $model->id,
                ]);
            }
        }
    }

    /**
     * Delete the model from the database.
     *
     * @param  \App\Models\Layer  $layer
     * @return bool|null|void
     */
    public function destroy($layer)
    {
        return $this->delete($layer);
    }
}
