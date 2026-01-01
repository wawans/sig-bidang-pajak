<?php

namespace App\Repositories;

use Illuminate\Support\Traits\ForwardsCalls;

abstract class Repository
{
    use ForwardsCalls;

    public function getModel()
    {
        return $this->model;
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function find($id, $columns = ['*'])
    {
        return $this->query()->find($id, $columns);
    }

    public function findOrFail($id, $columns = ['*'])
    {
        return $this->query()->findOrFail($id, $columns);
    }

    public function firstOrFail($columns = ['*'])
    {
        return $this->query()->firstOrFail($columns);
    }

    public function firstWhere($column, $operator = null, $value = null, $boolean = null)
    {
        return $this->query()->firstWhere($column, $operator, $value, $boolean);
    }

    public function query()
    {
        return $this->model->newQuery();
    }

    protected function create($attributes)
    {
        $model = $this->model->newInstance($attributes);

        $model->save();

        return $model;
    }

    protected function createQuietly($attributes)
    {
        $model = $this->model->newInstance($attributes);

        $model->saveQuietly();

        return $model;
    }

    protected function update($attributes, $id)
    {
        if (! ($id instanceof $this->model)) {
            $id = $this->query()->findOrFail($id);
        }

        $id->fill($attributes);
        $id->save();

        return $id;
    }

    protected function updateQuietly($attributes, $id)
    {
        if (! ($id instanceof $this->model)) {
            $id = $this->query()->findOrFail($id);
        }

        $id->fill($attributes);
        $id->saveQuietly();

        return $id;
    }

    protected function delete($id)
    {
        if (! ($id instanceof $this->model)) {
            $id = $this->query()->findOrFail($id);
        }

        return $id->delete();
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        return $this->forwardCallTo($this->query(), $method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
