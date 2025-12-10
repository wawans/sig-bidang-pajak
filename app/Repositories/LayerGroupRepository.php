<?php

namespace App\Repositories;

use App\Models\Layer;
use App\Repositories\Concerns\WithTable;
use Illuminate\Support\Collection;

/**
 * \App\Repositories\LayerGroupRepository
 *
 * @property \App\Models\LayerGroup $model
 *
 * @method \Illuminate\Database\Eloquent\Builder|\App\Models\LayerGroup query()
 * @method \App\Models\LayerGroup update(array $attributes, \App\Models\LayerGroup $layerGroup)
 */
class LayerGroupRepository extends Repository
{
    use WithTable;

    /**
     * Create a new repository instance.
     */
    public function __construct(protected \App\Models\LayerGroup $model) {}

    public function tableQuery()
    {
        return $this->query()->with(['parent', 'children.layer']);
    }

    /**
     * Create a new instance of the given model.
     *
     * @return \App\Models\LayerGroup
     */
    public function store(array $attributes)
    {
        $attributes = collect($attributes);

        $model = $this->create($attributes->except(['layers'])->toArray());

        $this->syncLayers($attributes, $model);

        return $model;
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  \App\Models\LayerGroup  $layerGroup
     * @return \App\Models\LayerGroup
     */
    public function edit($attributes, $layerGroup)
    {
        $attributes = collect($attributes);

        $model = $this->update($attributes->except(['permissions'])->toArray(), $layerGroup);

        $this->syncLayers($attributes, $model);

        return $model;
    }

    protected function syncLayers(Collection $attributes, \App\Models\LayerGroup $model)
    {
        if ($attributes->has('layers') && is_array($attributes->get('layers'))) {
            $layers = $attributes->get('layers');

            $model->children()->delete();

            // Error ?
            // $model->children()->createMany(array_map(fn ($v) => Layer::find($v), $layers));
            // $model->children()->createMany(collect($layers));

            collect($layers)->each(function ($layer) use ($model) {
                $model->children()->create(['layer_id' => $layer, 'layer_group_id' => $model->id]);
            });
        }
    }

    /**
     * Delete the model from the database.
     *
     * @param  \App\Models\LayerGroup  $layerGroup
     * @return bool|null|void
     */
    public function destroy($layerGroup)
    {
        return $this->delete($layerGroup);
    }
}
