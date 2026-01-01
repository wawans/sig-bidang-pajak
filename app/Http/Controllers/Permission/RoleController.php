<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\Role\StoreRoleRequest;
use App\Http\Requests\Permission\Role\UpdateRoleRequest;
use App\Models\Permission\Permission;
use App\Models\Permission\Role;
use App\Repositories\Permission\RoleRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Rap2hpoutre\FastExcel\FastExcel;

class RoleController extends Controller implements HasMiddleware
{
    use ApiTable;

    public function __construct(protected RoleRepository $repository) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:SETTING-ROLE-INDEX'),
            new Middleware('can:SETTING-ROLE-CREATE', only: ['create', 'store']),
            new Middleware('can:SETTING-ROLE-UPDATE', only: ['edit', 'update']),
            new Middleware('can:SETTING-ROLE-DELETE', only: ['destroy']),
        ];
    }

    public static function gate(): array
    {
        $user = auth()->user();

        return [
            'create' => $user->can('SETTING-ROLE-CREATE'),
            'update' => $user->can('SETTING-ROLE-UPDATE'),
            'delete' => $user->can('SETTING-ROLE-DELETE'),
        ];
    }

    private function form(): array
    {
        return [
            'permissions' => Permission::orderBy('name')->get()
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
        return Inertia::render('permission/role/index', [
            // 'table' => fn () => $this->repository->table(request()),
            'gate' => fn () => $this->gate(),
        ]);
    }

    public function export()
    {
        return (new FastExcel(Role::with(['permissions'])->filter(request()->all())->get()))->download('roles.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('permission/role/form', [
            'form' => $this->form(),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('role.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return Inertia::render('permission/role/show', [
            'model' => $this->repository->toArray($role->loadMissing('permissions')),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return Inertia::render('permission/role/form', [
            'model' => $this->repository->toArray($role->loadMissing('permissions')),
            'form' => $this->form(),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $model = $this->repository->edit($request->validated(), $role);

        return redirect()->route('role.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->repository->destroy($role);

        return redirect()->route('role.index')->with('success', 'Entri berhasil dihapus.');
    }
}
