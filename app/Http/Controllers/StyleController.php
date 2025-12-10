<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Requests\Style\StoreStyleRequest;
use App\Http\Requests\Style\UpdateStyleRequest;
use App\Models\Style;
use App\Repositories\StyleRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class StyleController extends Controller implements HasMiddleware
{
    use ApiTable;
    use Concerns\Map;

    public function __construct(protected StyleRepository $repository) {}

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

    protected function form()
    {
        return [
            'layers' => $this->layers(),
            'providers' => $this->providers(),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('style/index', [
            // 'table' => $this->repository->table(request()),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('style/form/index', [
            'gate' => $this->gate(),
            'form' => $this->form(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStyleRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('style.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Style $style)
    {
        return Inertia::render('style/show', [
            'model' => $this->repository->toArray($style),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Style $style)
    {
        return Inertia::render('style/form/index', [
            'model' => $this->repository->toArray($style),
            'gate' => $this->gate(),
            'form' => $this->form(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStyleRequest $request, Style $style)
    {
        $model = $this->repository->edit($request->validated(), $style);

        return redirect()->route('style.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Style $style)
    {
        $this->repository->destroy($style);

        return redirect()->route('style.index')->with('success', 'Entri berhasil dihapus.');
    }
}
