<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\Permission\UpdatePermissionRequest;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Repositories\Permission\PermissionRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Rap2hpoutre\FastExcel\FastExcel;

class PermissionController extends Controller implements HasMiddleware
{
    use ApiTable;

    public function __construct(protected PermissionRepository $repository) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:SETTING-PERMISSION-INDEX'),
            new Middleware('can:SETTING-PERMISSION-CREATE', only: ['create', 'store']),
            new Middleware('can:SETTING-PERMISSION-UPDATE', only: ['edit', 'update']),
            new Middleware('can:SETTING-PERMISSION-DELETE', only: ['destroy']),
        ];
    }

    public static function gate(): array
    {
        $user = auth()->user();

        return [
            'create' => $user->can('SETTING-PERMISSION-CREATE'),
            'update' => $user->can('SETTING-PERMISSION-UPDATE'),
            'delete' => $user->can('SETTING-PERMISSION-DELETE'),
        ];
    }

    private function form(): array
    {
        return [
            'roles' => Role::whereNot('name', 'SUPER ADMIN')->orderBy('name')->get()
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
        return Inertia::render('permission/permission/index', [
            // 'table' => fn () => $this->repository->table(request()),
            'gate' => fn () => $this->gate(),
        ]);
    }

    public function export()
    {
        return (new FastExcel(Permission::with(['roles'])->filter(request()->all())->get()))->download('permissions.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('permission/permission/form', [
            'form' => $this->form(),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('permission.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        return Inertia::render('permission/permission/show', [
            'model' => $this->repository->toArray($permission->loadMissing('roles')),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return Inertia::render('permission/permission/form', [
            'model' => $this->repository->toArray($permission->loadMissing('roles')),
            'form' => $this->form(),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $model = $this->repository->edit($request->validated(), $permission);

        return redirect()->route('permission.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $this->repository->destroy($permission);

        return redirect()->route('permission.index')->with('success', 'Entri berhasil dihapus.');
    }
}
