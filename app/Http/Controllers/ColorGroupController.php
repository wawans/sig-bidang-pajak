<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Requests\ColorGroup\StoreColorGroupRequest;
use App\Http\Requests\ColorGroup\UpdateColorGroupRequest;
use App\Models\Color\ColorGroup;
use App\Repositories\Color\ColorGroupRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class ColorGroupController extends Controller implements HasMiddleware
{
    use ApiTable;

    public function __construct(protected ColorGroupRepository $repository) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:SETTING-MAP-STYLE-INDEX'),
            new Middleware('can:SETTING-MAP-STYLE-CREATE', only: ['create', 'store']),
            new Middleware('can:SETTING-MAP-STYLE-UPDATE', only: ['edit', 'update']),
            new Middleware('can:SETTING-MAP-STYLE-DELETE', only: ['destroy']),
        ];
    }

    public static function gate(): array
    {
        $user = auth()->user();

        return [
            'create' => $user->can('SETTING-MAP-STYLE-CREATE'),
            'update' => $user->can('SETTING-MAP-STYLE-UPDATE'),
            'delete' => $user->can('SETTING-MAP-STYLE-DELETE'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('color-group/index', [
            'table' => $this->repository->table(request()),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('color-group/form', [
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreColorGroupRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('colorGroup.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ColorGroup $group)
    {
        return Inertia::render('color-group/show', [
            'model' => $this->repository->toArray($group),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ColorGroup $group)
    {
        return Inertia::render('color-group/form', [
            'model' => $this->repository->toArray($group),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateColorGroupRequest $request, ColorGroup $group)
    {
        $model = $this->repository->edit($request->validated(), $group);

        return redirect()->route('colorGroup.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ColorGroup $group)
    {
        $this->repository->destroy($group);

        return redirect()->route('colorGroup.index')->with('success', 'Entri berhasil dihapus.');
    }
}
