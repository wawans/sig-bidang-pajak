<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Requests\LayerGroup\StoreLayerGroupRequest;
use App\Http\Requests\LayerGroup\UpdateLayerGroupRequest;
use App\Models\Layer;
use App\Models\LayerGroup;
use App\Repositories\LayerGroupRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class LayerGroupController extends Controller implements HasMiddleware
{
    use ApiTable;

    public function __construct(protected LayerGroupRepository $repository) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:SETTING-MAP-LAYER-GROUP-INDEX'),
            new Middleware('can:SETTING-MAP-LAYER-GROUP-CREATE', only: ['create', 'store']),
            new Middleware('can:SETTING-MAP-LAYER-GROUP-UPDATE', only: ['edit', 'update']),
            new Middleware('can:SETTING-MAP-LAYER-GROUP-DELETE', only: ['destroy']),
        ];
    }

    public static function gate(): array
    {
        $user = auth()->user();

        return [
            'create' => $user->can('SETTING-MAP-LAYER-GROUP-CREATE'),
            'update' => $user->can('SETTING-MAP-LAYER-GROUP-UPDATE'),
            'delete' => $user->can('SETTING-MAP-LAYER-GROUP-DELETE'),
        ];
    }

    private function form(?LayerGroup $group = null): array
    {
        return [
            'groups' => LayerGroup::whereKeyNot($group)->orderBy('name')->get()
                ->transform(function ($v) {
                    return ['label' => $v->name, 'value' => $v->id];
                }),
            'layers' => Layer::when(is_null($group), function (Builder $query) {
                $query->doesntHave('member');
            })->when(! is_null($group), function (Builder $query) use ($group) {
                $query->whereDoesntHave('member', function (Builder $query) use ($group) {
                    $query->whereNot('layer_group_id', $group->id);
                });
            })->orderBy('name')->get()
                ->transform(function ($v) {
                    return ['label' => $v->name, 'value' => $v->id];
                }),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('layer-group/index', [
            // 'table' => $this->repository->table(request()),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('layer-group/form', [
            'gate' => $this->gate(),
            'form' => $this->form(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLayerGroupRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('layer-group.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LayerGroup $layerGroup)
    {
        return Inertia::render('layer-group/show', [
            'model' => $this->repository->toArray($layerGroup->loadMissing(['parent', 'children.layer'])),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LayerGroup $layerGroup)
    {
        return Inertia::render('layer-group/form', [
            'model' => $this->repository->toArray($layerGroup->loadMissing(['parent', 'children.layer'])),
            'gate' => $this->gate(),
            'form' => $this->form($layerGroup),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLayerGroupRequest $request, LayerGroup $layerGroup)
    {
        $model = $this->repository->edit($request->validated(), $layerGroup);

        return redirect()->route('layer-group.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LayerGroup $layerGroup)
    {
        $this->repository->destroy($layerGroup);

        return redirect()->route('layer-group.index')->with('success', 'Entri berhasil dihapus.');
    }
}
