<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiTable;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Support\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Rap2hpoutre\FastExcel\FastExcel;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    use ApiTable;

    public function __construct(protected UserRepository $repository) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:SETTING-USER-INDEX'),
            new Middleware('can:SETTING-USER-CREATE', only: ['create', 'store']),
            new Middleware('can:SETTING-USER-UPDATE', only: ['edit', 'update']),
            new Middleware('can:SETTING-USER-DELETE', only: ['destroy']),
        ];
    }

    public static function gate(): array
    {
        $user = auth()->user();

        return [
            'create' => $user->can('SETTING-USER-CREATE'),
            'update' => $user->can('SETTING-USER-UPDATE'),
            'delete' => $user->can('SETTING-USER-DELETE'),
        ];
    }

    private function form(): array
    {
        return [
            'roles' => Role::whereNotIn('name', ['SUPER ADMIN'])->orderBy('id')->get()->transform(function ($v) {
                return ['label' => $v->name, 'value' => $v->id];
            }),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('user/index', [
            // 'table' => fn () => $this->repository->table(request()),
            'gate' => fn () => $this->gate(),
        ]);
    }

    public function export()
    {
        return (new FastExcel(User::with(['roles'])->filter(request()->all())->get()))->download('users.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('user/form', [
            'form' => $this->form(),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $model = $this->repository->store($request->validated());

        return redirect()->route('user.show', $model->getRouteKey())->with('success', 'Entri berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return Inertia::render('user/show', [
            'model' => $this->repository->toArray($user->loadMissing('roles.permissions')),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('user/form', [
            'model' => $this->repository->toArray($user->loadMissing('roles.permissions')),
            'form' => $this->form(),
            'gate' => $this->gate(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $model = $this->repository->edit($request->validated(), $user);

        return redirect()->route('user.show', $model->getRouteKey())->with('success', 'Ubah Entri berhasil disimpan.');
    }

    public function patch(Request $request, User $user)
    {
        $validated = $request->validate([
            'status_email_verified' => ['required', 'boolean'],
        ]);

        $model = $this->repository->syncAccount(collect($validated), $user);

        return ApiResponse::data(['user' => $model]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->repository->destroy($user);

        return redirect()->route('user.index')->with('success', 'Entri berhasil dihapus.');
    }
}
