<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Requests\Layer\StoreLayerRequest;
use App\Http\Requests\Layer\UpdateLayerRequest;
use App\Models\Layer;
use App\Models\LayerGroup;
use App\Models\Style;
use App\Repositories\LayerRepository;
use App\Support\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class LayerController extends Controller implements HasMiddleware
{
    use ApiTable;

    public function __construct(protected LayerRepository $repository) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:SETTING-MAP-LAYER-INDEX'),
            new Middleware('can:SETTING-MAP-LAYER-CREATE', only: ['create', 'store']),
            new Middleware('can:SETTING-MAP-LAYER-UPDATE', only: ['edit', 'update']),
            new Middleware('can:SETTING-MAP-LAYER-DELETE', only: ['destroy']),
        ];
    }

    public static function gate(): array
    {
        $user = auth()->user();

        return [
            'create' => $user->can('SETTING-MAP-LAYER-CREATE'),
            'update' => $user->can('SETTING-MAP-LAYER-UPDATE'),
            'delete' => $user->can('SETTING-MAP-LAYER-DELETE'),
        ];
    }

    private function form(): array
    {
        return [
            'groups' => LayerGroup::orderBy('name')->get()
                ->transform(function ($v) {
                    return ['label' => $v->name, 'value' => $v->id];
                }),
            'styles' => Style::orderBy('name')->get()
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
        return Inertia::render('layer/index', [
            // 'table' => $this->repository->table(request()),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('layer/form', [
            'gate' => $this->gate(),
            'form' => $this->form(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLayerRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('layer.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Layer $layer)
    {
        return Inertia::render('layer/show', [
            'model' => $this->repository->toShow($layer),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Layer $layer)
    {
        return Inertia::render('layer/form', [
            'model' => $this->repository->toForm($layer),
            'gate' => $this->gate(),
            'form' => $this->form(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLayerRequest $request, Layer $layer)
    {
        $model = $this->repository->edit($request->validated(), $layer);

        return redirect()->route('layer.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    public function patch(Request $request, Layer $layer)
    {
        $validated = $request->validate([
            'autoload' => ['required', 'boolean'],
        ]);

        $layer->fill($validated)->saveQuietly();

        return ApiResponse::data(['layer' => $layer]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Layer $layer)
    {
        $this->repository->destroy($layer);

        return redirect()->route('layer.index')->with('success', 'Entri berhasil dihapus.');
    }
}
