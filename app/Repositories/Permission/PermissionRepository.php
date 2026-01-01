<?php

namespace App\Repositories\Permission;

use App\Models\Permission\Permission;
use App\Repositories\Concerns\WithTable;
use App\Repositories\Repository;

/**
 * \App\Repositories\Permission\PermissionRepository
 *
 * @method \Illuminate\Database\Eloquent\Builder|Permission query()
 * @method Permission update(array $attributes, Permission $id)
 */
class PermissionRepository extends Repository
{
    use WithTable;

    public $sortBy = 'name';

    public $sortDirection = 'asc';

    /**
     * Create a new repository instance.
     */
    public function __construct(protected Permission $model) {}

    public function tableQuery()
    {
        return $this->query()->with(['roles']);
    }

    /**
     * Create a new instance of the given model.
     *
     * @return Permission
     */
    public function store(array $attributes)
    {
        $attributes = collect($attributes);

        $model = $this->create($attributes->except(['roles'])->toArray());

        $this->syncRole($attributes, $model);

        return $model;
    }

    /**
     * Update the model in the database.
     *
     * @param  Permission  $id
     * @return Permission
     */
    public function edit(array $attributes, $id)
    {
        $attributes = collect($attributes);

        $model = $this->update($attributes->except(['roles'])->toArray(), $id);

        $this->syncRole($attributes, $model);

        return $model;
    }

    protected function syncRole($attributes, Permission $model)
    {
        if ($attributes->has('roles') && is_array($attributes->get('roles'))) {
            $roles = $attributes->get('roles');

            $model->syncRoles(array_map(fn ($v): int => $v, $roles));
            $model->saveQuietly();
        }
    }

    /**
     * Delete the model from the database.
     *
     * @param  Permission  $id
     * @return bool|null|void
     */
    public function destroy($id)
    {
        return $this->delete($id);
    }
}
