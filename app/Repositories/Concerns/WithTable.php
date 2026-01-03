<?php

namespace App\Repositories\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait WithTable
{
    /**
     * Pagination page `selector` name.
     *
     * @var string
     */
    public $pageName = 'page';

    /**
     * Pagination per page count `selector` name.
     *
     * @var string
     */
    public $perPageName = 'perPage';

    /**
     * Pagination per page count default value.
     *
     * @var string
     */
    public $perPage = 15;

    /**
     * Filter sort by `selector` name.
     *
     * @var string|array|null
     */
    public $sortByName = 'sortBy';

    /**
     * Filter sort direction `selector` name.
     *
     * @var string|array|null asc or desc
     */
    public $sortDirectionName = 'sortDirection';

    public function tableQuery()
    {
        return $this->query();
    }

    public function table(Request|Collection $request, ?callable $callback = null)
    {
        $perPage = $request->get($this->perPageName, $this->perPage);
        $page = $request->get($this->pageName, 1);

        $sortBy = $request->get($this->sortByName, $this->sortBy ?? null);
        $sortBy = $sortBy && Str::contains($sortBy, ',') ? collect(explode(',', $sortBy))->filter()->all() : $sortBy;
        $sortDirection = $request->get($this->sortDirectionName, $this->sortDirection ?? 'asc');
        $sortDirection = Str::contains($sortDirection, ',') ? collect(explode(',', $sortDirection))->filter()->all() : $sortDirection;

        $filters = $request->except([$this->perPageName, $this->pageName, $this->sortByName, $this->sortDirectionName]);

        $result = $this->tableQuery()
            ->when(method_exists($this, 'provideFilter') && ! blank($filters), function ($query) use ($filters) {
                $query->filter(is_array($filters) ? $filters : $filters->all(), $this->provideFilter());
            })
            ->when(! method_exists($this, 'provideFilter') && property_exists($this, 'model') && method_exists($this->model, 'provideFilter') && ! blank($filters), function ($query) use ($filters) {
                $query->filter(is_array($filters) ? $filters : $filters->all());
            })
            ->when(! is_null($sortBy) && ! blank($sortBy), function ($query) use ($sortBy, $sortDirection) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                if (is_array($sortBy) && is_array($sortDirection)) {
                    foreach ($sortBy as $index => $item) {
                        $query->orderBy($item, $sortDirection[$index] ?? 'asc');
                    }
                } elseif (is_array($sortBy) && ! is_array($sortDirection)) {
                    foreach ($sortBy as $item) {
                        $query->orderBy($item, $sortDirection);
                    }
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }
            })
            ->paginate($perPage, ['*'], $this->pageName, $page)
            ->onEachSide(2)
            ->withQueryString()
            ->through(
                $callback instanceof \Closure ? $callback : [
                    $this, isset($callback) && method_exists($this, $callback) ? $callback : 'toArray',
                ]
            );

        $d = [
            $this->sortByName => $sortBy,
            $this->sortDirectionName => $sortDirection,
        ];

        return collect(['filters' => array_merge($d, is_array($filters) ? $filters : $filters->all())])->merge($result);
    }

    public function toArray($model)
    {
        return array_merge($model instanceof \stdClass ? get_object_vars($model) : $model->toArray(),
            $model->timestamps ? [
                $model->getCreatedAtColumn() => $model->{$model->getCreatedAtColumn()}?->toDateTimeString(),
                $model->getUpdatedAtColumn() => $model->{$model->getUpdatedAtColumn()}?->toDateTimeString(),
            ] : [],
        );
    }
}
