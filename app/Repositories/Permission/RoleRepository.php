<?php

namespace App\Repositories\Permission;

use App\Models\Permission\Role;
use App\Repositories\Concerns\WithTable;
use App\Repositories\Repository;

/**
 * \App\Repositories\Permission\RoleRepository
 *
 * @method \Illuminate\Database\Eloquent\Builder|Role query()
 * @method Role update(array $attributes, Role $role)
 */
class RoleRepository extends Repository
{
    use WithTable;

    public $sortBy = 'id';

    public $sortDirection = 'desc';

    /**
     * Create a new repository instance.
     */
    public function __construct(protected Role $model) {}

    /**
     * Create a new instance of the given model.
     *
     * @return Role
     */
    public function store(array $attributes)
    {
        $attributes = collect($attributes);

        $model = $this->create($attributes->except(['permissions'])->toArray());

        $this->syncPermission($attributes, $model);

        return $model;
    }

    /**
     * Update the model in the database.
     *
     * @param  Role  $role
     * @return Role
     */
    public function edit(array $attributes, $model)
    {
        $attributes = collect($attributes);

        $model = $this->update($attributes->except(['permissions'])->toArray(), $model);

        $this->syncPermission($attributes, $model);

        return $model;
    }

    protected function syncPermission($attributes, Role $model)
    {
        if ($attributes->has('permissions') && is_array($attributes->get('permissions'))) {
            $permissions = $attributes->get('permissions');

            $model->syncPermissions(array_map(fn ($v): int => $v, $permissions));
            $model->saveQuietly();
        }
    }

    /**
     * Delete the model from the database.
     *
     * @param  Role  $role
     * @return bool|null|void
     */
    public function destroy($role)
    {
        return $this->delete($role);
    }
}
