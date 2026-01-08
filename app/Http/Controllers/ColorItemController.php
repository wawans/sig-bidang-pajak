<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorItem\StoreColorItemRequest;
use App\Http\Requests\ColorItem\UpdateColorItemRequest;
use App\Models\Color\ColorGroup;
use App\Models\Color\ColorItem;
use App\Repositories\Color\ColorItemRepository;
use App\Support\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class ColorItemController extends Controller implements HasMiddleware
{
    public function __construct(protected ColorItemRepository $repository) {}

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
    public function index(ColorGroup $group)
    {
        return Inertia::render('color-item/index', [
            // 'table' => $this->repository->table(request()),
            'gate' => $this->gate(),
            'group' => $group,
        ]);
    }

    public function table(ColorGroup $group)
    {
        $data = $this->repository->table(request()->mergeIfMissing(['colorGroupId' => $group->id])->collect());

        if (method_exists($this, 'gate')) {
            $data->put('gate', $this->gate());
        }

        return ApiResponse::make($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ColorGroup $group)
    {
        return Inertia::render('color-item/form', [
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreColorItemRequest $request, ColorGroup $group)
    {
        $model = $this->repository->store($request->validated(), $group);

        // return redirect()->route('colorItem.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
        return redirect()->back()->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ColorGroup $group, ColorItem $item)
    {
        return Inertia::render('color-item/show', [
            'model' => $this->repository->toArray($item),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ColorGroup $group, ColorItem $item)
    {
        return Inertia::render('color-item/form', [
            'model' => $this->repository->toArray($item),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateColorItemRequest $request, ColorGroup $group, ColorItem $item)
    {
        $model = $this->repository->edit($request->validated(), $item);

        // return redirect()->route('colorItem.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
        return redirect()->back()->with('success', 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ColorGroup $group, ColorItem $item)
    {
        $this->repository->destroy($item);

        // return redirect()->route('colorItem.index')->with('success', 'Entri berhasil dihapus.');
        return redirect()->back()->with('success', 'Entri berhasil dihapus.');
    }
}
